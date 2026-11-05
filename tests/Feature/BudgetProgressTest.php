<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Budget;
use App\Models\Category;
use App\Models\CategoryType;
use App\Models\User;
use App\Services\BudgetService;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_budget_progress_calculates_percent_and_level(): void
    {
        $user = User::factory()->create();
        $accountType = AccountType::create(['code' => 'CASH', 'name' => 'Kas']);
        $account = Account::create([
            'account_code' => 'CASH-01', 'account_name' => 'Kas', 'account_type_id' => $accountType->id,
            'opening_balance' => 5000000, 'current_balance' => 5000000, 'status' => 'ACTIVE',
        ]);
        $expenseType = CategoryType::create(['code' => 'EXPENSE', 'name' => 'Pengeluaran']);
        $makanan = Category::create(['category_type_id' => $expenseType->id, 'name' => 'Makanan', 'slug' => 'makanan']);

        $budget = Budget::create([
            'name' => 'Budget Makanan', 'period_type' => 'MONTHLY', 'category_id' => $makanan->id,
            'amount' => 1000000, 'alert_threshold_percent' => 80, 'status' => 'ACTIVE',
        ]);

        app(TransactionService::class)->createExpense([
            'transaction_date' => now()->toDateString(),
            'account_id' => $account->id,
            'category_id' => $makanan->id,
            'amount' => 850000,
        ], $user);

        $progress = app(BudgetService::class)->progress($budget);

        $this->assertSame(850000.0, $progress['actual']);
        $this->assertSame(85.0, $progress['percent']);
        $this->assertSame('warning', $progress['level']);
        $this->assertDatabaseHas('budget_items', [
            'budget_id' => $budget->id,
            'period_year' => now()->year,
            'period_month' => now()->month,
            'actual_amount' => 850000,
        ]);
    }

    public function test_budget_level_is_exceeded_when_over_100_percent(): void
    {
        $user = User::factory()->create();
        $accountType = AccountType::create(['code' => 'CASH', 'name' => 'Kas']);
        $account = Account::create([
            'account_code' => 'CASH-01', 'account_name' => 'Kas', 'account_type_id' => $accountType->id,
            'opening_balance' => 5000000, 'current_balance' => 5000000, 'status' => 'ACTIVE',
        ]);
        $expenseType = CategoryType::create(['code' => 'EXPENSE', 'name' => 'Pengeluaran']);
        $makanan = Category::create(['category_type_id' => $expenseType->id, 'name' => 'Makanan', 'slug' => 'makanan']);

        $budget = Budget::create([
            'name' => 'Budget Makanan', 'period_type' => 'MONTHLY', 'category_id' => $makanan->id,
            'amount' => 500000, 'alert_threshold_percent' => 80, 'status' => 'ACTIVE',
        ]);

        app(TransactionService::class)->createExpense([
            'transaction_date' => now()->toDateString(),
            'account_id' => $account->id,
            'category_id' => $makanan->id,
            'amount' => 600000,
        ], $user);

        $progress = app(BudgetService::class)->progress($budget);

        $this->assertSame('exceeded', $progress['level']);
        $this->assertSame(0.0, $progress['remaining']);
    }
}
