<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Reconciliation;
use App\Models\ReconciliationItem;
use App\Models\Transaction;
use App\Models\User;
use App\Support\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReconciliationService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Reconciliation::query()
            ->with('account')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{account_id:int, statement_date:string, statement_balance:float, notes?:?string}  $data
     */
    public function start(array $data, User $user): Reconciliation
    {
        $account = Account::findOrFail($data['account_id']);
        $systemBalance = (float) $account->current_balance;
        $difference = Money::sub((string) $data['statement_balance'], (string) $systemBalance);

        $reconciliation = Reconciliation::create([
            'account_id' => $account->id,
            'statement_date' => $data['statement_date'],
            'statement_balance' => $data['statement_balance'],
            'system_balance' => $systemBalance,
            'difference' => $difference,
            'status' => 'IN_PROGRESS',
            'notes' => $data['notes'] ?? null,
            'created_by' => $user->id,
        ]);

        $this->auditLog->created($reconciliation, 'reconciliation');

        return $reconciliation;
    }

    /**
     * Transactions for this reconciliation's account up to the statement date,
     * annotated with their matched state for this reconciliation.
     *
     * @return Collection<int, Transaction>
     */
    public function candidateTransactions(Reconciliation $reconciliation): Collection
    {
        $matchedTransactionIds = $reconciliation->items()->where('is_matched', true)->pluck('transaction_id');

        return Transaction::query()
            ->where(function ($q) use ($reconciliation) {
                $q->where('account_id', $reconciliation->account_id)
                    ->orWhere('destination_account_id', $reconciliation->account_id);
            })
            ->whereDate('transaction_date', '<=', $reconciliation->statement_date)
            ->where(fn ($q) => $q->where('is_reconciled', false)->orWhereIn('id', $matchedTransactionIds))
            ->orderByDesc('transaction_date')
            ->get()
            ->map(function (Transaction $transaction) use ($matchedTransactionIds) {
                $transaction->setAttribute('is_matched_here', $matchedTransactionIds->contains($transaction->id));

                return $transaction;
            });
    }

    public function toggleMatch(Reconciliation $reconciliation, Transaction $transaction): void
    {
        if ($reconciliation->status === 'COMPLETED') {
            throw new RuntimeException('Rekonsiliasi ini sudah selesai.');
        }

        DB::transaction(function () use ($reconciliation, $transaction) {
            $item = ReconciliationItem::firstOrNew([
                'reconciliation_id' => $reconciliation->id,
                'transaction_id' => $transaction->id,
            ]);

            $item->is_matched = ! $item->is_matched;
            $item->matched_at = $item->is_matched ? now() : null;
            $item->save();

            $transaction->update(['is_reconciled' => $item->is_matched]);
        });
    }

    public function complete(Reconciliation $reconciliation): Reconciliation
    {
        $reconciliation->update([
            'status' => 'COMPLETED',
            'completed_at' => now(),
        ]);

        $this->auditLog->updated($reconciliation, 'reconciliation', ['status' => 'IN_PROGRESS']);

        return $reconciliation;
    }

    public function matchedTotal(Reconciliation $reconciliation): float
    {
        return (float) $reconciliation->items()->where('is_matched', true)->count();
    }
}
