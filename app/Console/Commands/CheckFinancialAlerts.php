<?php

namespace App\Console\Commands;

use App\Models\Budget;
use App\Models\Debt;
use App\Models\Receivable;
use App\Services\AlertNotificationService;
use App\Services\BudgetService;
use Illuminate\Console\Command;

class CheckFinancialAlerts extends Command
{
    protected $signature = 'keuang:check-alerts';

    protected $description = 'Check budgets, debts, and receivables and notify relevant users of alerts.';

    public function handle(AlertNotificationService $notifier, BudgetService $budgetService): int
    {
        $this->checkBudgets($notifier, $budgetService);
        $this->checkDebts($notifier);
        $this->checkReceivables($notifier);

        $this->info('Financial alerts checked.');

        return self::SUCCESS;
    }

    private function checkBudgets(AlertNotificationService $notifier, BudgetService $budgetService): void
    {
        $users = $notifier->usersWithPermission('budget.manage');

        foreach (Budget::where('status', 'ACTIVE')->get() as $budget) {
            $progress = $budgetService->progress($budget);

            if ($progress['level'] === 'exceeded') {
                foreach ($users as $user) {
                    $notifier->notifyOncePerDay($user, 'budget_exceeded', 'Budget Terlampaui',
                        "Budget \"{$budget->name}\" sudah terlampaui ({$progress['percent']}%).", route('budgets.index'));
                }
            } elseif ($progress['level'] === 'warning') {
                foreach ($users as $user) {
                    $notifier->notifyOncePerDay($user, 'budget_warning', 'Budget Hampir Habis',
                        "Budget \"{$budget->name}\" sudah terpakai {$progress['percent']}%.", route('budgets.index'));
                }
            }
        }
    }

    private function checkDebts(AlertNotificationService $notifier): void
    {
        $users = $notifier->usersWithPermission('debt.manage');

        $dueSoon = Debt::whereIn('status', ['OUTSTANDING', 'PARTIALLY_PAID'])
            ->whereBetween('due_date', [now()->toDateString(), now()->addDays(3)->toDateString()])
            ->get();

        foreach ($dueSoon as $debt) {
            foreach ($users as $user) {
                $notifier->notifyOncePerDay($user, 'debt_due_soon', 'Hutang Mendekati Jatuh Tempo',
                    "Hutang kepada {$debt->creditor_name} ({$debt->debt_number}) jatuh tempo {$debt->due_date->format('d/m/Y')}.", route('debts.index'));
            }
        }

        $overdue = Debt::whereNotIn('status', ['PAID'])
            ->whereDate('due_date', '<', now()->toDateString())
            ->get();

        foreach ($overdue as $debt) {
            $debt->update(['status' => 'OVERDUE']);

            foreach ($users as $user) {
                $notifier->notifyOncePerDay($user, 'debt_overdue', 'Hutang Jatuh Tempo (Overdue)',
                    "Hutang kepada {$debt->creditor_name} ({$debt->debt_number}) telah melewati jatuh tempo.", route('debts.index'));
            }
        }
    }

    private function checkReceivables(AlertNotificationService $notifier): void
    {
        $users = $notifier->usersWithPermission('receivable.manage');

        $dueSoon = Receivable::whereIn('status', ['OUTSTANDING', 'PARTIALLY_PAID'])
            ->whereBetween('due_date', [now()->toDateString(), now()->addDays(3)->toDateString()])
            ->get();

        foreach ($dueSoon as $receivable) {
            foreach ($users as $user) {
                $notifier->notifyOncePerDay($user, 'receivable_due_soon', 'Piutang Mendekati Jatuh Tempo',
                    "Piutang dari {$receivable->debtor_name} ({$receivable->receivable_number}) jatuh tempo {$receivable->due_date->format('d/m/Y')}.", route('receivables.index'));
            }
        }

        $overdue = Receivable::whereNotIn('status', ['PAID'])
            ->whereDate('due_date', '<', now()->toDateString())
            ->get();

        foreach ($overdue as $receivable) {
            $receivable->update(['status' => 'OVERDUE']);

            foreach ($users as $user) {
                $notifier->notifyOncePerDay($user, 'receivable_overdue', 'Piutang Jatuh Tempo (Overdue)',
                    "Piutang dari {$receivable->debtor_name} ({$receivable->receivable_number}) telah melewati jatuh tempo.", route('receivables.index'));
            }
        }
    }
}
