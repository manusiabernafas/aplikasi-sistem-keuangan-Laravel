<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategoryType;
use App\Models\Receivable;
use App\Models\ReceivablePayment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReceivableService
{
    public function __construct(
        private readonly TransactionNumberService $numberService,
        private readonly TransactionService $transactionService,
        private readonly AuditLogService $auditLog,
    ) {}

    public function paginate(?string $status, int $perPage = 15): LengthAwarePaginator
    {
        return Receivable::query()
            ->withSum('payments', 'amount')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{debtor_name:string, amount:float, receivable_date:string, due_date:string, account_id?:?int, notes?:?string}  $data
     */
    public function create(array $data, User $user): Receivable
    {
        $receivable = Receivable::create([
            'receivable_number' => $this->numberService->generateWithPrefix('RCV', now()),
            'debtor_name' => $data['debtor_name'],
            'amount' => $data['amount'],
            'receivable_date' => $data['receivable_date'],
            'due_date' => $data['due_date'],
            'status' => 'OUTSTANDING',
            'account_id' => $data['account_id'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => $user->id,
        ]);

        $this->auditLog->created($receivable, 'receivable');

        return $receivable;
    }

    /**
     * @param  array{debtor_name:string, amount:float, receivable_date:string, due_date:string, account_id?:?int, notes?:?string}  $data
     */
    public function update(Receivable $receivable, array $data): Receivable
    {
        $original = $receivable->getOriginal();

        $receivable->fill([
            'debtor_name' => $data['debtor_name'],
            'amount' => $data['amount'],
            'receivable_date' => $data['receivable_date'],
            'due_date' => $data['due_date'],
            'account_id' => $data['account_id'] ?? null,
            'notes' => $data['notes'] ?? null,
        ])->save();

        $this->auditLog->updated($receivable, 'receivable', $original);

        return $receivable;
    }

    public function delete(Receivable $receivable): void
    {
        if ($receivable->payments()->exists()) {
            throw new RuntimeException('Piutang tidak dapat dihapus karena sudah memiliki riwayat pembayaran.');
        }

        $this->auditLog->deleted($receivable, 'receivable');

        $receivable->delete();
    }

    /**
     * @param  array{payment_date:string, amount:float, account_id:int, notes?:?string}  $data
     */
    public function recordPayment(Receivable $receivable, array $data, User $user): ReceivablePayment
    {
        if ($data['amount'] > $receivable->remaining()) {
            throw new RuntimeException('Nominal pembayaran melebihi sisa piutang.');
        }

        return DB::transaction(function () use ($receivable, $data, $user) {
            $category = $this->paymentCategory();

            $transaction = $this->transactionService->createIncome([
                'transaction_date' => $data['payment_date'],
                'account_id' => $data['account_id'],
                'category_id' => $category->id,
                'amount' => $data['amount'],
                'description' => "Penerimaan piutang: {$receivable->debtor_name} ({$receivable->receivable_number})",
            ], $user);

            $payment = ReceivablePayment::create([
                'receivable_id' => $receivable->id,
                'payment_date' => $data['payment_date'],
                'amount' => $data['amount'],
                'account_id' => $data['account_id'],
                'transaction_id' => $transaction->id,
                'notes' => $data['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            $receivable->update([
                'status' => $receivable->remaining() <= 0 ? 'PAID' : 'PARTIALLY_PAID',
            ]);

            $this->auditLog->created($payment, 'receivable_payment');

            return $payment;
        });
    }

    private function paymentCategory(): Category
    {
        $incomeType = CategoryType::where('code', 'INCOME')->firstOrFail();

        return Category::firstOrCreate(
            ['category_type_id' => $incomeType->id, 'parent_id' => null, 'name' => 'Penerimaan Piutang'],
            ['slug' => 'penerimaan-piutang', 'is_active' => true]
        );
    }
}
