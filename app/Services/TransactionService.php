<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Support\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TransactionService
{
    public function __construct(
        private readonly LedgerService $ledger,
        private readonly TransactionNumberService $numberService,
        private readonly AuditLogService $auditLog,
    ) {}

    /**
     * @param  array{search?:?string, type?:?string, account_id?:?int, category_id?:?int, status?:?string, date_from?:?string, date_to?:?string, amount_min?:?float, amount_max?:?float, sort?:?string, direction?:?string}  $filters
     */
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $sort = $filters['sort'] ?? 'transaction_date';
        $direction = $filters['direction'] ?? 'desc';

        if (! in_array($sort, ['transaction_date', 'amount', 'transaction_number'], true)) {
            $sort = 'transaction_date';
        }

        return Transaction::query()
            ->with(['account:id,account_name', 'destinationAccount:id,account_name', 'category:id,name', 'createdBy:id,name'])
            ->when($filters['search'] ?? null, fn (Builder $q, $s) => $q->where(function (Builder $q) use ($s) {
                $q->where('transaction_number', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%")
                    ->orWhere('reference_number', 'like', "%{$s}%");
            }))
            ->when($filters['type'] ?? null, fn (Builder $q, $t) => $q->where('transaction_type', $t))
            ->when($filters['account_id'] ?? null, fn (Builder $q, $id) => $q->where(fn (Builder $q) => $q->where('account_id', $id)->orWhere('destination_account_id', $id)))
            ->when($filters['category_id'] ?? null, fn (Builder $q, $id) => $q->where('category_id', $id))
            ->when($filters['status'] ?? null, fn (Builder $q, $s) => $q->where('status', $s))
            ->when($filters['date_from'] ?? null, fn (Builder $q, $d) => $q->whereDate('transaction_date', '>=', $d))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $d) => $q->whereDate('transaction_date', '<=', $d))
            ->when($filters['amount_min'] ?? null, fn (Builder $q, $a) => $q->where('amount', '>=', $a))
            ->when($filters['amount_max'] ?? null, fn (Builder $q, $a) => $q->where('amount', '<=', $a))
            ->orderBy($sort, $direction === 'asc' ? 'asc' : 'desc')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{transaction_date:string, account_id:int, category_id:int, amount:float, description?:?string, reference_number?:?string, notes?:?string, tag_ids?:array<int,int>}  $data
     */
    public function createIncome(array $data, User $user): Transaction
    {
        return $this->createSimple(Transaction::TYPE_INCOME, $data, $user);
    }

    /**
     * @param  array{transaction_date:string, account_id:int, category_id:int, amount:float, description?:?string, reference_number?:?string, notes?:?string, tag_ids?:array<int,int>}  $data
     */
    public function createExpense(array $data, User $user): Transaction
    {
        return $this->createSimple(Transaction::TYPE_EXPENSE, $data, $user);
    }

    /**
     * @param  array{transaction_date:string, account_id:int, destination_account_id:int, amount:float, description?:?string, reference_number?:?string, notes?:?string}  $data
     */
    public function createTransfer(array $data, User $user): Transaction
    {
        if ((int) $data['account_id'] === (int) $data['destination_account_id']) {
            throw new RuntimeException('Account sumber dan tujuan tidak boleh sama.');
        }

        return DB::transaction(function () use ($data, $user) {
            $sourceAccount = $this->lockActiveAccount((int) $data['account_id']);
            $this->lockActiveAccount((int) $data['destination_account_id']);
            $this->assertSufficientBalance($sourceAccount, $data['amount']);

            $transaction = Transaction::create([
                'transaction_number' => $this->numberService->generate(Transaction::TYPE_TRANSFER, now()),
                'transaction_date' => $data['transaction_date'],
                'transaction_type' => Transaction::TYPE_TRANSFER,
                'account_id' => $data['account_id'],
                'destination_account_id' => $data['destination_account_id'],
                'category_id' => null,
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $this->ledger->post($transaction);
            $this->auditLog->created($transaction, 'transaction');

            return $transaction;
        });
    }

    /**
     * @param  array{transaction_date:string, account_id:int, category_id:int, amount:float, description?:?string, reference_number?:?string, notes?:?string, tag_ids?:array<int,int>}  $data
     */
    public function update(Transaction $transaction, array $data, User $user): Transaction
    {
        $this->assertNotReconciled($transaction, $user);

        if ($transaction->transaction_type === Transaction::TYPE_TRANSFER) {
            return $this->updateTransfer($transaction, $data, $user);
        }

        return DB::transaction(function () use ($transaction, $data, $user) {
            $original = $transaction->getOriginal();

            $this->ledger->reverse($transaction->fresh());

            $account = $this->lockActiveAccount((int) $data['account_id']);

            if ($transaction->transaction_type === Transaction::TYPE_EXPENSE) {
                $this->assertSufficientBalance($account, $data['amount']);
            }

            $transaction->fill([
                'transaction_date' => $data['transaction_date'],
                'account_id' => $data['account_id'],
                'category_id' => $data['category_id'],
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'updated_by' => $user->id,
            ])->save();

            $this->ledger->post($transaction->fresh());

            if (array_key_exists('tag_ids', $data)) {
                $transaction->tags()->sync($data['tag_ids'] ?? []);
            }

            $this->auditLog->updated($transaction, 'transaction', $original);

            return $transaction->fresh();
        });
    }

    /**
     * @param  array{transaction_date:string, account_id:int, destination_account_id:int, amount:float, description?:?string, reference_number?:?string, notes?:?string}  $data
     */
    private function updateTransfer(Transaction $transaction, array $data, User $user): Transaction
    {
        if ((int) $data['account_id'] === (int) $data['destination_account_id']) {
            throw new RuntimeException('Account sumber dan tujuan tidak boleh sama.');
        }

        return DB::transaction(function () use ($transaction, $data, $user) {
            $original = $transaction->getOriginal();

            $this->ledger->reverse($transaction->fresh());

            $sourceAccount = $this->lockActiveAccount((int) $data['account_id']);
            $this->lockActiveAccount((int) $data['destination_account_id']);
            $this->assertSufficientBalance($sourceAccount, $data['amount']);

            $transaction->fill([
                'transaction_date' => $data['transaction_date'],
                'account_id' => $data['account_id'],
                'destination_account_id' => $data['destination_account_id'],
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'updated_by' => $user->id,
            ])->save();

            $this->ledger->post($transaction->fresh());

            $this->auditLog->updated($transaction, 'transaction', $original);

            return $transaction->fresh();
        });
    }

    public function delete(Transaction $transaction, User $user): void
    {
        $this->assertNotReconciled($transaction, $user);

        DB::transaction(function () use ($transaction, $user) {
            $this->ledger->reverse($transaction);

            $transaction->update([
                'status' => 'VOID',
                'updated_by' => $user->id,
            ]);

            $transaction->delete();

            $this->auditLog->deleted($transaction, 'transaction');
        });
    }

    /**
     * @param  array{transaction_date:string, account_id:int, category_id:int, amount:float, description?:?string, reference_number?:?string, notes?:?string, tag_ids?:array<int,int>}  $data
     */
    private function createSimple(string $type, array $data, User $user): Transaction
    {
        return DB::transaction(function () use ($type, $data, $user) {
            $account = $this->lockActiveAccount((int) $data['account_id']);

            $this->assertCategoryMatchesType((int) $data['category_id'], $type);

            if ($type === Transaction::TYPE_EXPENSE) {
                $this->assertSufficientBalance($account, $data['amount']);
            }

            $transaction = Transaction::create([
                'transaction_number' => $this->numberService->generate($type, now()),
                'transaction_date' => $data['transaction_date'],
                'transaction_type' => $type,
                'account_id' => $data['account_id'],
                'category_id' => $data['category_id'],
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $this->ledger->post($transaction);

            if (! empty($data['tag_ids'])) {
                $transaction->tags()->sync($data['tag_ids']);
            }

            $this->auditLog->created($transaction, 'transaction');

            return $transaction;
        });
    }

    private function lockActiveAccount(int $accountId): Account
    {
        $account = Account::whereKey($accountId)->lockForUpdate()->firstOrFail();

        if (! $account->isActive()) {
            throw new RuntimeException("Account \"{$account->account_name}\" tidak aktif.");
        }

        return $account;
    }

    private function assertSufficientBalance(Account $account, float|string $amount): void
    {
        if (Money::compare((string) $account->current_balance, (string) $amount) < 0) {
            throw new RuntimeException("Saldo \"{$account->account_name}\" tidak mencukupi.");
        }
    }

    private function assertCategoryMatchesType(int $categoryId, string $transactionType): void
    {
        $expectedCode = $transactionType === Transaction::TYPE_INCOME ? 'INCOME' : 'EXPENSE';

        $matches = Category::whereKey($categoryId)
            ->whereHas('categoryType', fn (Builder $q) => $q->where('code', $expectedCode))
            ->exists();

        if (! $matches) {
            throw new RuntimeException('Kategori tidak sesuai dengan tipe transaksi.');
        }
    }

    private function assertNotReconciled(Transaction $transaction, User $user): void
    {
        if ($transaction->is_reconciled && ! $user->hasPermission('reconciliation.override')) {
            throw new RuntimeException('Transaksi yang sudah direkonsiliasi tidak dapat diubah.');
        }
    }
}
