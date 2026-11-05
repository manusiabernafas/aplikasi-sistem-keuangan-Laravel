<?php

namespace App\Services;

use App\Models\RecurringTransaction;
use App\Models\RecurringTransactionRun;
use App\Models\User;
use App\Notifications\FinancialAlertNotification;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class RecurringTransactionService
{
    public function __construct(
        private readonly TransactionService $transactionService,
        private readonly AuditLogService $auditLog,
        private readonly AlertNotificationService $alertNotifier,
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return RecurringTransaction::query()
            ->with(['account', 'destinationAccount', 'category'])
            ->orderBy('next_run_date')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{name:string, transaction_type:string, amount:float, account_id:int, destination_account_id?:?int, category_id?:?int, frequency:string, interval_count?:int, day_of_month?:?int, day_of_week?:?int, start_date:string, end_date?:?string}  $data
     */
    public function create(array $data, User $user): RecurringTransaction
    {
        $recurring = RecurringTransaction::create([
            'name' => $data['name'],
            'transaction_type' => $data['transaction_type'],
            'amount' => $data['amount'],
            'account_id' => $data['account_id'],
            'destination_account_id' => $data['destination_account_id'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'frequency' => $data['frequency'],
            'interval_count' => $data['interval_count'] ?? 1,
            'day_of_month' => $data['day_of_month'] ?? null,
            'day_of_week' => $data['day_of_week'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'next_run_date' => $data['start_date'],
            'status' => 'ACTIVE',
            'created_by' => $user->id,
        ]);

        $this->auditLog->created($recurring, 'recurring_transaction');

        return $recurring;
    }

    /**
     * @param  array{name:string, amount:float, account_id:int, destination_account_id?:?int, category_id?:?int, frequency:string, interval_count?:int, day_of_month?:?int, day_of_week?:?int, end_date?:?string, status?:string}  $data
     */
    public function update(RecurringTransaction $recurring, array $data): RecurringTransaction
    {
        $original = $recurring->getOriginal();

        $recurring->fill([
            'name' => $data['name'],
            'amount' => $data['amount'],
            'account_id' => $data['account_id'],
            'destination_account_id' => $data['destination_account_id'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'frequency' => $data['frequency'],
            'interval_count' => $data['interval_count'] ?? $recurring->interval_count,
            'day_of_month' => $data['day_of_month'] ?? null,
            'day_of_week' => $data['day_of_week'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'status' => $data['status'] ?? $recurring->status,
        ])->save();

        $this->auditLog->updated($recurring, 'recurring_transaction', $original);

        return $recurring;
    }

    public function delete(RecurringTransaction $recurring): void
    {
        $this->auditLog->deleted($recurring, 'recurring_transaction');

        $recurring->delete();
    }

    public function calculateNextRunDate(RecurringTransaction $recurring, CarbonInterface $from): Carbon
    {
        $interval = max(1, $recurring->interval_count);
        $next = Carbon::parse($from);

        return match ($recurring->frequency) {
            'DAILY' => $next->addDays($interval),
            'WEEKLY' => $next->addWeeks($interval),
            'MONTHLY' => $this->applyDayOfMonth($next->addMonthsNoOverflow($interval), $recurring->day_of_month),
            'YEARLY' => $next->addYearsNoOverflow($interval),
            default => $next->addMonthsNoOverflow($interval),
        };
    }

    private function applyDayOfMonth(Carbon $date, ?int $dayOfMonth): Carbon
    {
        if (! $dayOfMonth) {
            return $date;
        }

        return $date->day(min($dayOfMonth, $date->daysInMonth));
    }

    /**
     * Process every recurring transaction that is due, guarding against
     * duplicate execution via the recurring_transaction_runs unique key.
     *
     * @return array{processed: int, failed: int}
     */
    public function processDue(User $systemUser, ?CarbonInterface $today = null): array
    {
        $today = ($today ?? now())->startOfDay();
        $processed = 0;
        $failed = 0;

        $dueRecurrences = RecurringTransaction::query()
            ->where('status', 'ACTIVE')
            ->whereDate('next_run_date', '<=', $today)
            ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today))
            ->get();

        foreach ($dueRecurrences as $recurring) {
            if (RecurringTransactionRun::where('recurring_transaction_id', $recurring->id)
                ->whereDate('run_date', $today)
                ->exists()) {
                continue;
            }

            try {
                DB::transaction(function () use ($recurring, $today, $systemUser) {
                    $payload = [
                        'transaction_date' => $today->toDateString(),
                        'account_id' => $recurring->account_id,
                        'amount' => $recurring->amount,
                        'description' => "Recurring: {$recurring->name}",
                    ];

                    $transaction = match ($recurring->transaction_type) {
                        'INCOME' => $this->transactionService->createIncome($payload + ['category_id' => $recurring->category_id], $systemUser),
                        'EXPENSE' => $this->transactionService->createExpense($payload + ['category_id' => $recurring->category_id], $systemUser),
                        'TRANSFER' => $this->transactionService->createTransfer($payload + ['destination_account_id' => $recurring->destination_account_id], $systemUser),
                        default => throw new RuntimeException("Unknown recurring transaction type: {$recurring->transaction_type}"),
                    };

                    RecurringTransactionRun::create([
                        'recurring_transaction_id' => $recurring->id,
                        'run_date' => $today->toDateString(),
                        'transaction_id' => $transaction->id,
                        'status' => 'SUCCESS',
                    ]);

                    $recurring->update([
                        'last_run_date' => $today->toDateString(),
                        'next_run_date' => $this->calculateNextRunDate($recurring, $today)->toDateString(),
                    ]);
                });

                foreach ($this->alertNotifier->usersWithPermission('budget.manage') as $user) {
                    $user->notify(new FinancialAlertNotification(
                        'recurring_created',
                        'Transaksi Berulang Dibuat',
                        "Transaksi berulang \"{$recurring->name}\" berhasil dibuat untuk tanggal {$today->format('d/m/Y')}.",
                        route('recurring-transactions.index')
                    ));
                }

                $processed++;
            } catch (Throwable $exception) {
                RecurringTransactionRun::create([
                    'recurring_transaction_id' => $recurring->id,
                    'run_date' => $today->toDateString(),
                    'status' => 'FAILED',
                    'error_message' => $exception->getMessage(),
                ]);

                Log::warning("Recurring transaction #{$recurring->id} failed: {$exception->getMessage()}");
                $failed++;
            }
        }

        return compact('processed', 'failed');
    }
}
