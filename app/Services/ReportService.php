<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Debt;
use App\Models\Receivable;
use App\Models\Transaction;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * @param  array{account_id?:?int, category_id?:?int, search?:?string}  $filters
     */
    public function transactionsOfType(string $type, CarbonInterface $from, CarbonInterface $to, array $filters = []): Collection
    {
        return Transaction::ofType($type)
            ->betweenDates($from, $to)
            ->with(['account:id,account_name', 'category:id,name'])
            ->when($filters['account_id'] ?? null, fn (Builder $q, $id) => $q->where('account_id', $id))
            ->when($filters['category_id'] ?? null, fn (Builder $q, $id) => $q->where('category_id', $id))
            ->when($filters['search'] ?? null, fn (Builder $q, $s) => $q->where('description', 'like', "%{$s}%"))
            ->orderBy('transaction_date')
            ->get();
    }

    /**
     * @return array<int, array{category: string, total: float}>
     */
    public function byCategory(string $type, CarbonInterface $from, CarbonInterface $to): array
    {
        return Transaction::ofType($type)
            ->betweenDates($from, $to)
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category:id,name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['category' => $row->category?->name ?? 'Lainnya', 'total' => (float) $row->total])
            ->all();
    }

    /**
     * @return array{buckets: array<int, array{label:string, income:float, expense:float, net:float}>, total_income: float, total_expense: float, total_net: float}
     */
    public function cashFlow(CarbonInterface $from, CarbonInterface $to): array
    {
        $period = Carbon::parse($from)->startOfMonth();
        $end = Carbon::parse($to)->startOfMonth();
        $buckets = [];

        while ($period->lte($end)) {
            $income = (float) Transaction::ofType(Transaction::TYPE_INCOME)
                ->whereYear('transaction_date', $period->year)->whereMonth('transaction_date', $period->month)
                ->sum('amount');
            $expense = (float) Transaction::ofType(Transaction::TYPE_EXPENSE)
                ->whereYear('transaction_date', $period->year)->whereMonth('transaction_date', $period->month)
                ->sum('amount');

            $buckets[] = [
                'label' => $period->translatedFormat('M Y'),
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
            ];

            $period->addMonth();
        }

        return [
            'buckets' => $buckets,
            'total_income' => array_sum(array_column($buckets, 'income')),
            'total_expense' => array_sum(array_column($buckets, 'expense')),
            'total_net' => array_sum(array_column($buckets, 'net')),
        ];
    }

    /**
     * @return array{income: float, expense: float, net_profit: float, income_by_category: array<int, array{category:string,total:float}>, expense_by_category: array<int, array{category:string,total:float}>}
     */
    public function profitLoss(CarbonInterface $from, CarbonInterface $to): array
    {
        $income = (float) Transaction::ofType(Transaction::TYPE_INCOME)->betweenDates($from, $to)->sum('amount');
        $expense = (float) Transaction::ofType(Transaction::TYPE_EXPENSE)->betweenDates($from, $to)->sum('amount');

        return [
            'income' => $income,
            'expense' => $expense,
            'net_profit' => $income - $expense,
            'income_by_category' => $this->byCategory(Transaction::TYPE_INCOME, $from, $to),
            'expense_by_category' => $this->byCategory(Transaction::TYPE_EXPENSE, $from, $to),
        ];
    }

    /**
     * @return array{assets: array<string, float>, liabilities: array<string, float>, total_assets: float, total_liabilities: float, net_worth: float, trend: array<int, array{label:string, net_worth:float}>}
     */
    public function netWorth(): array
    {
        $cashLike = (float) Account::active()->sum('current_balance');
        $receivablesRemaining = Receivable::withSum('payments', 'amount')
            ->whereIn('status', ['OUTSTANDING', 'PARTIALLY_PAID'])
            ->get()->sum(fn (Receivable $r) => $r->remaining());
        $debtsRemaining = Debt::withSum('payments', 'amount')
            ->whereIn('status', ['OUTSTANDING', 'PARTIALLY_PAID'])
            ->get()->sum(fn (Debt $d) => $d->remaining());

        $assets = [
            'Kas, Bank & E-Wallet' => $cashLike,
            'Piutang' => $receivablesRemaining,
        ];
        $liabilities = [
            'Hutang' => $debtsRemaining,
        ];

        $totalAssets = array_sum($assets);
        $totalLiabilities = array_sum($liabilities);

        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->copy()->subMonthsNoOverflow($i);
            $trend[] = ['label' => $month->translatedFormat('M Y'), 'net_worth' => $totalAssets - $totalLiabilities];
        }

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'net_worth' => $totalAssets - $totalLiabilities,
            'trend' => $trend,
        ];
    }

    /**
     * @return array<int, array{label:string, income:float, expense:float, net:float, transaction_count:int}>
     */
    public function monthlySummary(int $year): array
    {
        $rows = [];

        for ($month = 1; $month <= 12; $month++) {
            $income = (float) Transaction::ofType(Transaction::TYPE_INCOME)
                ->whereYear('transaction_date', $year)->whereMonth('transaction_date', $month)->sum('amount');
            $expense = (float) Transaction::ofType(Transaction::TYPE_EXPENSE)
                ->whereYear('transaction_date', $year)->whereMonth('transaction_date', $month)->sum('amount');
            $count = Transaction::whereYear('transaction_date', $year)->whereMonth('transaction_date', $month)->count();

            $rows[] = [
                'label' => Carbon::create($year, $month, 1)->translatedFormat('F'),
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
                'transaction_count' => $count,
            ];
        }

        return $rows;
    }
}
