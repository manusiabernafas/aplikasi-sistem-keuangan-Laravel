<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Category;
use App\Models\CategoryType;
use App\Models\Role;
use App\Models\User;
use App\Services\TransactionService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class TransactionLedgerTest extends TestCase
{
    use RefreshDatabase;

    private TransactionService $service;

    private User $user;

    private Account $bca;

    private Category $gaji;

    private Category $makanan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);

        $this->user = User::factory()->create();
        $this->user->roles()->attach(Role::where('slug', 'super-admin')->firstOrFail());

        $bankType = AccountType::create(['code' => 'BANK', 'name' => 'Bank']);
        $this->bca = Account::create([
            'account_code' => 'BCA-001',
            'account_name' => 'BCA',
            'account_type_id' => $bankType->id,
            'opening_balance' => 1000000,
            'current_balance' => 1000000,
            'status' => 'ACTIVE',
        ]);

        $incomeType = CategoryType::create(['code' => 'INCOME', 'name' => 'Pemasukan']);
        $expenseType = CategoryType::create(['code' => 'EXPENSE', 'name' => 'Pengeluaran']);

        $this->gaji = Category::create(['category_type_id' => $incomeType->id, 'name' => 'Gaji', 'slug' => 'gaji']);
        $this->makanan = Category::create(['category_type_id' => $expenseType->id, 'name' => 'Makanan', 'slug' => 'makanan']);

        $this->service = app(TransactionService::class);
    }

    public function test_expense_reduces_account_balance_correctly(): void
    {
        $this->service->createExpense([
            'transaction_date' => now()->toDateString(),
            'account_id' => $this->bca->id,
            'category_id' => $this->makanan->id,
            'amount' => 250000,
        ], $this->user);

        $this->assertSame('750000.00', $this->bca->fresh()->current_balance);
    }

    public function test_income_increases_account_balance_correctly(): void
    {
        $this->service->createIncome([
            'transaction_date' => now()->toDateString(),
            'account_id' => $this->bca->id,
            'category_id' => $this->gaji->id,
            'amount' => 5000000,
        ], $this->user);

        $this->assertSame('6000000.00', $this->bca->fresh()->current_balance);
    }

    public function test_expense_is_rejected_when_balance_insufficient(): void
    {
        $this->expectException(RuntimeException::class);

        try {
            $this->service->createExpense([
                'transaction_date' => now()->toDateString(),
                'account_id' => $this->bca->id,
                'category_id' => $this->makanan->id,
                'amount' => 2000000,
            ], $this->user);
        } finally {
            $this->assertSame('1000000.00', $this->bca->fresh()->current_balance);
            $this->assertDatabaseCount('transactions', 0);
        }
    }

    public function test_transfer_moves_balance_between_accounts_without_affecting_income_expense(): void
    {
        $kas = Account::create([
            'account_code' => 'CASH-001',
            'account_name' => 'Kas',
            'account_type_id' => $this->bca->account_type_id,
            'opening_balance' => 0,
            'current_balance' => 0,
            'status' => 'ACTIVE',
        ]);

        $transaction = $this->service->createTransfer([
            'transaction_date' => now()->toDateString(),
            'account_id' => $this->bca->id,
            'destination_account_id' => $kas->id,
            'amount' => 300000,
        ], $this->user);

        $this->assertSame('TRANSFER', $transaction->transaction_type);
        $this->assertSame('700000.00', $this->bca->fresh()->current_balance);
        $this->assertSame('300000.00', $kas->fresh()->current_balance);
    }

    public function test_delete_transaction_reverses_balance_effect(): void
    {
        $transaction = $this->service->createExpense([
            'transaction_date' => now()->toDateString(),
            'account_id' => $this->bca->id,
            'category_id' => $this->makanan->id,
            'amount' => 250000,
        ], $this->user);

        $this->assertSame('750000.00', $this->bca->fresh()->current_balance);

        $this->service->delete($transaction, $this->user);

        $this->assertSame('1000000.00', $this->bca->fresh()->current_balance);
        $this->assertSoftDeleted('transactions', ['id' => $transaction->id]);
    }

    public function test_update_transaction_recalculates_old_and_new_balance_effect(): void
    {
        $transaction = $this->service->createExpense([
            'transaction_date' => now()->toDateString(),
            'account_id' => $this->bca->id,
            'category_id' => $this->makanan->id,
            'amount' => 250000,
        ], $this->user);

        $this->service->update($transaction, [
            'transaction_date' => now()->toDateString(),
            'account_id' => $this->bca->id,
            'category_id' => $this->makanan->id,
            'amount' => 400000,
        ], $this->user);

        $this->assertSame('600000.00', $this->bca->fresh()->current_balance);
    }

    public function test_update_rolls_back_when_new_amount_exceeds_balance(): void
    {
        $transaction = $this->service->createExpense([
            'transaction_date' => now()->toDateString(),
            'account_id' => $this->bca->id,
            'category_id' => $this->makanan->id,
            'amount' => 250000,
        ], $this->user);

        $this->expectException(RuntimeException::class);

        try {
            $this->service->update($transaction, [
                'transaction_date' => now()->toDateString(),
                'account_id' => $this->bca->id,
                'category_id' => $this->makanan->id,
                'amount' => 5000000,
            ], $this->user);
        } finally {
            $this->assertSame('750000.00', $this->bca->fresh()->current_balance);
            $this->assertSame('250000.00', $transaction->fresh()->amount);
        }
    }

    public function test_transaction_numbers_are_unique_and_formatted(): void
    {
        $first = $this->service->createExpense([
            'transaction_date' => now()->toDateString(),
            'account_id' => $this->bca->id,
            'category_id' => $this->makanan->id,
            'amount' => 10000,
        ], $this->user);

        $second = $this->service->createExpense([
            'transaction_date' => now()->toDateString(),
            'account_id' => $this->bca->id,
            'category_id' => $this->makanan->id,
            'amount' => 20000,
        ], $this->user);

        $this->assertNotSame($first->transaction_number, $second->transaction_number);
        $this->assertMatchesRegularExpression('/^EXP-\d{8}-\d{6}$/', $first->transaction_number);
    }
}
