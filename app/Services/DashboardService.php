<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Budget;
use App\Models\Debt;
use App\Models\FinancialGoal;
use App\Models\Receivable;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(private readonly BudgetService $budgetService) {}

    /**
     * @return array{label: string, from: Carbon, to: Carbon}
     */
    public function resolvePeriod(string $period, ?string $from, ?string $to): array
    {
        $now = now();

        return match ($period) {
            'today' => ['label' => 'Hari ini', 'from' => $now->copy()->startOfDay(), 'to' => $now->copy()->endOfDay()],
            'week' => ['label' => 'Minggu ini', 'from' => $now->copy()->startOfWeek(), 'to' => $now->copy()->endOfWeek()],
            'last_month' => ['label' => 'Bulan lalu', 'from' => $now->copy()->subMonthNoOverflow()->startOfMonth(), 'to' => $now->copy()->subMonthNoOverflow()->endOfMonth()],
            'year' => ['label' => 'Tahun ini', 'from' => $now->copy()->startOfYear(), 'to' => $now->copy()->endOfYear()],
            'custom' => [
                'label' => 'Custom',
                'from' => $from ? Carbon::parse($from)->startOfDay() : $now->copy()->startOfMonth(),
                'to' => $to ? Carbon::parse($to)->endOfDay() : $now->copy()->endOfDay(),
            ],
            default => ['label' => 'Bulan ini', 'from' => $now->copy()->startOfMonth(), 'to' => $now->copy()->endOfMonth()],
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(Carbon $from, Carbon $to): array
    {
        $totalBalance = Account::active()->sum('current_balance');

        $income = (float) Transaction::ofType(Transaction::TYPE_INCOME)->betweenDates($from, $to)->sum('amount');
        $expense = (float) Transaction::ofType(Transaction::TYPE_EXPENSE)->betweenDates($from, $to)->sum('amount');

        $transactionCount = Transaction::betweenDates($from, $to)->count();

        return [
            'total_balance' => (float) $totalBalance,
            'income' => $income,
            'expense' => $expense,
            'net_cash_flow' => $income - $expense,
            'transaction_count' => $transactionCount,
        ];
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function recentTransactions(int $limit = 6): Collection
    {
        return Transaction::with(['account:id,account_name', 'destinationAccount:id,account_name', 'category:id,name'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * 6-month income vs expense trend ending this month.
     *
     * @return array{labels: array<int, string>, income: array<int, float>, expense: array<int, float>}
     */
    public function incomeVsExpenseTrend(): array
    {
        $labels = [];
        $income = [];
        $expense = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->copy()->subMonthsNoOverflow($i);
            $labels[] = $month->translatedFormat('M Y');

            $income[] = (float) Transaction::ofType(Transaction::TYPE_INCOME)
                ->whereYear('transaction_date', $month->year)
                ->whereMonth('transaction_date', $month->month)
                ->sum('amount');

            $expense[] = (float) Transaction::ofType(Transaction::TYPE_EXPENSE)
                ->whereYear('transaction_date', $month->year)
                ->whereMonth('transaction_date', $month->month)
                ->sum('amount');
        }

        return compact('labels', 'income', 'expense');
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, float>}
     */
    public function expenseByCategory(Carbon $from, Carbon $to): array
    {
        $rows = Transaction::ofType(Transaction::TYPE_EXPENSE)
            ->betweenDates($from, $to)
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category:id,name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'labels' => $rows->map(fn ($row) => $row->category?->name ?? 'Lainnya')->all(),
            'values' => $rows->map(fn ($row) => (float) $row->total)->all(),
        ];
    }

    /**
     * @return Collection<int, array{budget: Budget, progress: array<string, mixed>}>
     */
    public function activeBudgetsProgress(int $limit = 4): Collection
    {
        return Budget::query()
            ->where('status', 'ACTIVE')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (Budget $budget) => [
                'budget' => $budget,
                'progress' => $this->budgetService->progress($budget),
            ]);
    }

    /**
     * @return Collection<int, FinancialGoal>
     */
    public function activeGoals(int $limit = 3): Collection
    {
        return FinancialGoal::where('status', 'IN_PROGRESS')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Debt>
     */
    public function upcomingDebts(int $limit = 5): Collection
    {
        return Debt::withSum('payments', 'amount')
            ->whereIn('status', ['OUTSTANDING', 'PARTIALLY_PAID'])
            ->orderBy('due_date')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Receivable>
     */
    public function upcomingReceivables(int $limit = 5): Collection
    {
        return Receivable::withSum('payments', 'amount')
            ->whereIn('status', ['OUTSTANDING', 'PARTIALLY_PAID'])
            ->orderBy('due_date')
            ->limit($limit)
            ->get();
    }
}
