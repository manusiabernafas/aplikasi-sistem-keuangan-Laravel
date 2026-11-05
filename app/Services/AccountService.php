<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use App\Support\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AccountService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    /**
     * @param  array{search?:?string, account_type_id?:?int, status?:?string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return Account::query()
            ->with('accountType')
            ->when($filters['search'] ?? null, fn (Builder $q, $s) => $q->where(function (Builder $q) use ($s) {
                $q->where('account_name', 'like', "%{$s}%")->orWhere('account_code', 'like', "%{$s}%");
            }))
            ->when($filters['account_type_id'] ?? null, fn (Builder $q, $id) => $q->where('account_type_id', $id))
            ->when($filters['status'] ?? null, fn (Builder $q, $s) => $q->where('status', $s))
            ->orderBy('account_name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{account_code:string, account_name:string, account_type_id:int, opening_balance:float, currency?:string, description?:?string, status?:string}  $data
     */
    public function create(array $data, User $user): Account
    {
        $account = Account::create([
            'account_code' => $data['account_code'],
            'account_name' => $data['account_name'],
            'account_type_id' => $data['account_type_id'],
            'opening_balance' => $data['opening_balance'],
            'current_balance' => $data['opening_balance'],
            'currency' => $data['currency'] ?? 'IDR',
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'ACTIVE',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->auditLog->created($account, 'account');

        return $account;
    }

    /**
     * @param  array{account_code:string, account_name:string, account_type_id:int, opening_balance:float, currency?:string, description?:?string, status?:string}  $data
     */
    public function update(Account $account, array $data, User $user): Account
    {
        return DB::transaction(function () use ($account, $data, $user) {
            $locked = Account::whereKey($account->id)->lockForUpdate()->firstOrFail();
            $original = $locked->getOriginal();

            $openingDelta = Money::sub((string) $data['opening_balance'], (string) $locked->opening_balance);

            $locked->fill([
                'account_code' => $data['account_code'],
                'account_name' => $data['account_name'],
                'account_type_id' => $data['account_type_id'],
                'opening_balance' => $data['opening_balance'],
                'current_balance' => Money::add((string) $locked->current_balance, $openingDelta),
                'currency' => $data['currency'] ?? $locked->currency,
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? $locked->status,
                'updated_by' => $user->id,
            ])->save();

            $this->auditLog->updated($locked, 'account', $original);

            return $locked;
        });
    }

    public function delete(Account $account): void
    {
        $hasTransactions = Transaction::where('account_id', $account->id)
            ->orWhere('destination_account_id', $account->id)
            ->exists();

        if ($hasTransactions) {
            throw new RuntimeException('Account tidak dapat dihapus karena sudah memiliki transaksi.');
        }

        $this->auditLog->deleted($account, 'account');

        $account->delete();
    }
}
