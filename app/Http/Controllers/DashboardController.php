<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService) {}

    public function index(Request $request)
    {
        $period = $request->string('period')->toString() ?: 'month';
        ['label' => $periodLabel, 'from' => $from, 'to' => $to] = $this->dashboardService->resolvePeriod(
            $period,
            $request->string('date_from')->toString() ?: null,
            $request->string('date_to')->toString() ?: null,
        );

        $summary = $this->dashboardService->summary($from, $to);
        $recentTransactions = $this->dashboardService->recentTransactions();
        $trend = $this->dashboardService->incomeVsExpenseTrend();
        $expenseByCategory = $this->dashboardService->expenseByCategory($from, $to);
        $budgetsProgress = $request->user()->hasPermission('budget.manage') ? $this->dashboardService->activeBudgetsProgress() : collect();
        $goals = $request->user()->hasPermission('budget.manage') ? $this->dashboardService->activeGoals() : collect();
        $upcomingDebts = $request->user()->hasPermission('debt.manage') ? $this->dashboardService->upcomingDebts() : collect();
        $upcomingReceivables = $request->user()->hasPermission('receivable.manage') ? $this->dashboardService->upcomingReceivables() : collect();

        return view('dashboard.index', [
            'summary' => $summary,
            'period' => $period,
            'periodLabel' => $periodLabel,
            'dateFrom' => $from->toDateString(),
            'dateTo' => $to->toDateString(),
            'recentTransactions' => $recentTransactions,
            'trend' => $trend,
            'expenseByCategory' => $expenseByCategory,
            'budgetsProgress' => $budgetsProgress,
            'goals' => $goals,
            'upcomingDebts' => $upcomingDebts,
            'upcomingReceivables' => $upcomingReceivables,
        ]);
    }
}
