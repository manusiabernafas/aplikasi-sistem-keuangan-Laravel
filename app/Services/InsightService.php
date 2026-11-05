<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Transaction;
use Illuminate\Support\Collection;

class InsightService
{
    public function __construct(private readonly BudgetService $budgetService) {}

    /**
     * @return Collection<int, array{type: string, message: string}>
     */
    public function generate(): Collection
    {
        $insights = collect();

        $now = now();
        $lastMonth = $now->copy()->subMonthNoOverflow();

        $expenseThisMonth = (float) Transaction::ofType(Transaction::TYPE_EXPENSE)
            ->whereYear('transaction_date', $now->year)->whereMonth('transaction_date', $now->month)
            ->sum('amount');
        $expenseLastMonth = (float) Transaction::ofType(Transaction::TYPE_EXPENSE)
            ->whereYear('transaction_date', $lastMonth->year)->whereMonth('transaction_date', $lastMonth->month)
            ->sum('amount');

        $incomeThisMonth = (float) Transaction::ofType(Transaction::TYPE_INCOME)
            ->whereYear('transaction_date', $now->year)->whereMonth('transaction_date', $now->month)
            ->sum('amount');

        // Overall expense trend vs last month.
        if ($expenseLastMonth > 0) {
            $change = round((($expenseThisMonth - $expenseLastMonth) / $expenseLastMonth) * 100, 1);

            if (abs($change) >= 1) {
                $direction = $change > 0 ? 'naik' : 'turun';
                $insights->push([
                    'type' => $change > 0 ? 'warning' : 'success',
                    'message' => "Pengeluaran bulan ini {$direction} ".abs($change).'% dibanding bulan lalu.',
                ]);
            }
        }

        // Biggest expense category this month.
        $topCategory = Transaction::ofType(Transaction::TYPE_EXPENSE)
            ->whereYear('transaction_date', $now->year)->whereMonth('transaction_date', $now->month)
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category:id,name')
            ->orderByDesc('total')
            ->first();

        if ($topCategory && $topCategory->category) {
            $insights->push([
                'type' => 'info',
                'message' => "Kategori {$topCategory->category->name} merupakan pengeluaran terbesar bulan ini (Rp ".number_format((float) $topCategory->total, 0, ',', '.').').',
            ]);
        }

        // Average daily spend this month.
        $daysElapsed = max(1, $now->day);
        $dailyAverage = $expenseThisMonth / $daysElapsed;
        if ($expenseThisMonth > 0) {
            $insights->push([
                'type' => 'info',
                'message' => 'Anda menghabiskan rata-rata Rp '.number_format($dailyAverage, 0, ',', '.').' per hari bulan ini.',
            ]);
        }

        // Cash flow this month.
        $netCashFlow = $incomeThisMonth - $expenseThisMonth;
        $insights->push([
            'type' => $netCashFlow >= 0 ? 'success' : 'danger',
            'message' => 'Cash flow bulan ini '.($netCashFlow >= 0 ? 'positif' : 'negatif').' Rp '.number_format(abs($netCashFlow), 0, ',', '.').'.',
        ]);

        // Budget alerts.
        foreach (Budget::where('status', 'ACTIVE')->get() as $budget) {
            $progress = $this->budgetService->progress($budget);

            if ($progress['level'] === 'exceeded') {
                $insights->push([
                    'type' => 'danger',
                    'message' => "Budget \"{$budget->name}\" sudah terlampaui ({$progress['percent']}%).",
                ]);
            } elseif ($progress['level'] === 'warning') {
                $remainingPercent = max(0, round(100 - $progress['percent'], 1));
                $insights->push([
                    'type' => 'warning',
                    'message' => "Budget \"{$budget->name}\" tersisa {$remainingPercent}%.",
                ]);
            }
        }

        return $insights;
    }
}
