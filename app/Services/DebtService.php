<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategoryType;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DebtService
{
    public function __construct(
        private readonly TransactionNumberService $numberService,
        private readonly TransactionService $transactionService,
        private readonly AuditLogService $auditLog,
    ) {}

    public function paginate(?string $status, int $perPage = 15): LengthAwarePaginator
    {
        return Debt::query()
            ->withSum('payments', 'amount')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{creditor_name:string, amount:float, debt_date:string, due_date:string, account_id?:?int, notes?:?string}  $data
     */
    public function create(array $data, User $user): Debt
    {
        $debt = Debt::create([
            'debt_number' => $this->numberService->generateWithPrefix('DBT', now()),
            'creditor_name' => $data['creditor_name'],
            'amount' => $data['amount'],
            'debt_date' => $data['debt_date'],
            'due_date' => $data['due_date'],
            'status' => 'OUTSTANDING',
            'account_id' => $data['account_id'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => $user->id,
        ]);

        $this->auditLog->created($debt, 'debt');

        return $debt;
    }

    /**
     * @param  array{creditor_name:string, amount:float, debt_date:string, due_date:string, account_id?:?int, notes?:?string}  $data
     */
    public function update(Debt $debt, array $data): Debt
    {
        $original = $debt->getOriginal();

        $debt->fill([
            'creditor_name' => $data['creditor_name'],
            'amount' => $data['amount'],
            'debt_date' => $data['debt_date'],
            'due_date' => $data['due_date'],
            'account_id' => $data['account_id'] ?? null,
            'notes' => $data['notes'] ?? null,
        ])->save();

        $this->auditLog->updated($debt, 'debt', $original);

        return $debt;
    }

    public function delete(Debt $debt): void
    {
        if ($debt->payments()->exists()) {
            throw new RuntimeException('Hutang tidak dapat dihapus karena sudah memiliki riwayat pembayaran.');
        }

        $this->auditLog->deleted($debt, 'debt');

        $debt->delete();
    }

    /**
     * @param  array{payment_date:string, amount:float, account_id:int, notes?:?string}  $data
     */
    public function recordPayment(Debt $debt, array $data, User $user): DebtPayment
    {
        if ($data['amount'] > $debt->remaining()) {
            throw new RuntimeException('Nominal pembayaran melebihi sisa hutang.');
        }

        return DB::transaction(function () use ($debt, $data, $user) {
            $category = $this->paymentCategory();

            $transaction = $this->transactionService->createExpense([
                'transaction_date' => $data['payment_date'],
                'account_id' => $data['account_id'],
                'category_id' => $category->id,
                'amount' => $data['amount'],
                'description' => "Pembayaran hutang: {$debt->creditor_name} ({$debt->debt_number})",
            ], $user);

            $payment = DebtPayment::create([
                'debt_id' => $debt->id,
                'payment_date' => $data['payment_date'],
                'amount' => $data['amount'],
                'account_id' => $data['account_id'],
                'transaction_id' => $transaction->id,
                'notes' => $data['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            $debt->update([
                'status' => $debt->remaining() <= 0 ? 'PAID' : 'PARTIALLY_PAID',
            ]);

            $this->auditLog->created($payment, 'debt_payment');

            return $payment;
        });
    }

    private function paymentCategory(): Category
    {
        $expenseType = CategoryType::where('code', 'EXPENSE')->firstOrFail();

        return Category::firstOrCreate(
            ['category_type_id' => $expenseType->id, 'parent_id' => null, 'name' => 'Pembayaran Hutang'],
            ['slug' => 'pembayaran-hutang', 'is_active' => true]
        );
    }
}
