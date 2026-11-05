<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\FinancialGoalController;
use App\Http\Controllers\InsightController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\ReconciliationController;
use App\Http\Controllers\RecurringTransactionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('admin')->name('admin.')->middleware('permission:user.manage')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('roles', RoleController::class)->except(['show']);
    });

    Route::middleware('permission:transaction.view')->group(function () {
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    });

    Route::middleware('permission:transaction.create')->group(function () {
        Route::get('/transactions/income/create', [TransactionController::class, 'createIncome'])->name('transactions.income.create');
        Route::post('/transactions/income', [TransactionController::class, 'storeIncome'])->name('transactions.income.store');
        Route::get('/transactions/expense/create', [TransactionController::class, 'createExpense'])->name('transactions.expense.create');
        Route::post('/transactions/expense', [TransactionController::class, 'storeExpense'])->name('transactions.expense.store');
        Route::get('/transactions/transfer/create', [TransactionController::class, 'createTransfer'])->name('transactions.transfer.create');
        Route::post('/transactions/transfer', [TransactionController::class, 'storeTransfer'])->name('transactions.transfer.store');
        Route::post('/transactions/quick', [TransactionController::class, 'quickStore'])->name('transactions.quick.store');
    });

    Route::middleware('permission:transaction.update')->group(function () {
        Route::get('/transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
        Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    });

    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])
        ->middleware('permission:transaction.delete')
        ->name('transactions.destroy');

    Route::middleware('permission:account.manage')->group(function () {
        Route::resource('accounts', AccountController::class)->except(['show']);
    });

    Route::middleware('permission:category.manage')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('tags', TagController::class)->except(['show']);
    });

    Route::get('/audit-logs', [AuditLogController::class, 'index'])
        ->middleware('permission:audit.view')
        ->name('audit-logs.index');

    Route::middleware('permission:budget.manage')->group(function () {
        Route::resource('budgets', BudgetController::class)->except(['show']);
        Route::resource('recurring-transactions', RecurringTransactionController::class)->except(['show']);
        Route::resource('goals', FinancialGoalController::class)->except(['show']);
        Route::post('/goals/{financial_goal}/contribute', [FinancialGoalController::class, 'contribute'])->name('goals.contribute');
    });

    Route::middleware('permission:debt.manage')->group(function () {
        Route::resource('debts', DebtController::class)->except(['show']);
        Route::post('/debts/{debt}/pay', [DebtController::class, 'pay'])->name('debts.pay');
    });

    Route::middleware('permission:receivable.manage')->group(function () {
        Route::resource('receivables', ReceivableController::class)->except(['show']);
        Route::post('/receivables/{receivable}/pay', [ReceivableController::class, 'pay'])->name('receivables.pay');
    });

    Route::middleware('permission:reconciliation.manage')->prefix('reconciliations')->name('reconciliations.')->group(function () {
        Route::get('/', [ReconciliationController::class, 'index'])->name('index');
        Route::get('/create', [ReconciliationController::class, 'create'])->name('create');
        Route::post('/', [ReconciliationController::class, 'store'])->name('store');
        Route::get('/{reconciliation}', [ReconciliationController::class, 'show'])->name('show');
        Route::post('/{reconciliation}/toggle/{transaction}', [ReconciliationController::class, 'toggleMatch'])->name('toggle');
        Route::post('/{reconciliation}/complete', [ReconciliationController::class, 'complete'])->name('complete');
    });

    Route::middleware('permission:report.view')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/cash-flow', [ReportController::class, 'cashFlow'])->name('cash-flow');
        Route::get('/income', [ReportController::class, 'income'])->name('income');
        Route::get('/expense', [ReportController::class, 'expense'])->name('expense');
        Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/net-worth', [ReportController::class, 'netWorth'])->name('net-worth');
        Route::get('/summary', [ReportController::class, 'summary'])->name('summary');
    });

    Route::get('/insights', [InsightController::class, 'index'])
        ->middleware('permission:report.view')
        ->name('insights.index');

    Route::middleware('permission:backup.manage')->prefix('backups')->name('backups.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::post('/', [BackupController::class, 'store'])->name('store');
        Route::get('/{filename}/download', [BackupController::class, 'download'])->name('download');
        Route::delete('/{filename}', [BackupController::class, 'destroy'])->name('destroy');
    });

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    Route::middleware('permission:setting.manage')->group(function () {
        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

require __DIR__.'/auth.php';
