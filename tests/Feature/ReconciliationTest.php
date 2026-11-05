<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Category;
use App\Models\CategoryType;
use App\Models\User;
use App\Services\ReconciliationService;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_matching_a_transaction_marks_it_reconciled_and_unmatching_reverts(): void
    {
        $user = User::factory()->create();
        $accountType = AccountType::create(['code' => 'BANK', 'name' => 'Bank']);
        $account = Account::create([
            'account_code' => 'BCA-01', 'account_name' => 'BCA', 'account_type_id' => $accountType->id,
            'opening_balance' => 1000000, 'current_balance' => 1000000, 'status' => 'ACTIVE',
        ]);
        $expenseType = CategoryType::create(['code' => 'EXPENSE', 'name' => 'Pengeluaran']);
        $makanan = Category::create(['category_type_id' => $expenseType->id, 'name' => 'Makanan', 'slug' => 'makanan']);

        $transaction = app(TransactionService::class)->createExpense([
            'transaction_date' => now()->toDateString(),
            'account_id' => $account->id,
            'category_id' => $makanan->id,
            'amount' => 100000,
        ], $user);

        $service = app(ReconciliationService::class);

        $reconciliation = $service->start([
            'account_id' => $account->id,
            'statement_date' => now()->toDateString(),
            'statement_balance' => 900000,
        ], $user);

        $this->assertSame('900000.00', $reconciliation->statement_balance);
        $this->assertSame('900000.00', $reconciliation->system_balance);
        $this->assertSame('0.00', $reconciliation->difference);

        $service->toggleMatch($reconciliation, $transaction);
        $this->assertTrue($transaction->fresh()->is_reconciled);

        $service->toggleMatch($reconciliation, $transaction);
        $this->assertFalse($transaction->fresh()->is_reconciled);
    }

    public function test_reconciled_transaction_cannot_be_updated_without_override_permission(): void
    {
        $user = User::factory()->create();
        $accountType = AccountType::create(['code' => 'BANK', 'name' => 'Bank']);
        $account = Account::create([
            'account_code' => 'BCA-01', 'account_name' => 'BCA', 'account_type_id' => $accountType->id,
            'opening_balance' => 1000000, 'current_balance' => 1000000, 'status' => 'ACTIVE',
        ]);
        $expenseType = CategoryType::create(['code' => 'EXPENSE', 'name' => 'Pengeluaran']);
        $makanan = Category::create(['category_type_id' => $expenseType->id, 'name' => 'Makanan', 'slug' => 'makanan']);

        $transaction = app(TransactionService::class)->createExpense([
            'transaction_date' => now()->toDateString(),
            'account_id' => $account->id,
            'category_id' => $makanan->id,
            'amount' => 100000,
        ], $user);

        $transaction->update(['is_reconciled' => true]);

        $this->assertFalse($user->can('update', $transaction->fresh()));
    }
}
