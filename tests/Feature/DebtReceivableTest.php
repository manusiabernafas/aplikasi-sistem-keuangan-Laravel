<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\CategoryType;
use App\Models\User;
use App\Services\DebtService;
use App\Services\ReceivableService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class DebtReceivableTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $accountType = AccountType::create(['code' => 'BANK', 'name' => 'Bank']);
        $this->account = Account::create([
            'account_code' => 'BCA-01', 'account_name' => 'BCA', 'account_type_id' => $accountType->id,
            'opening_balance' => 10000000, 'current_balance' => 10000000, 'status' => 'ACTIVE',
        ]);

        CategoryType::create(['code' => 'INCOME', 'name' => 'Pemasukan']);
        CategoryType::create(['code' => 'EXPENSE', 'name' => 'Pengeluaran']);
    }

    public function test_debt_payment_reduces_account_balance_and_updates_status(): void
    {
        $debt = app(DebtService::class)->create([
            'creditor_name' => 'Bank ABC',
            'amount' => 5000000,
            'debt_date' => now()->toDateString(),
            'due_date' => now()->addMonth()->toDateString(),
        ], $this->user);

        $this->assertMatchesRegularExpression('/^DBT-\d{8}-\d{6}$/', $debt->debt_number);

        app(DebtService::class)->recordPayment($debt, [
            'payment_date' => now()->toDateString(),
            'amount' => 2000000,
            'account_id' => $this->account->id,
        ], $this->user);

        $this->assertSame('8000000.00', $this->account->fresh()->current_balance);
        $this->assertSame('PARTIALLY_PAID', $debt->fresh()->status);
        $this->assertSame(3000000.0, $debt->fresh()->remaining());

        app(DebtService::class)->recordPayment($debt->fresh(), [
            'payment_date' => now()->toDateString(),
            'amount' => 3000000,
            'account_id' => $this->account->id,
        ], $this->user);

        $this->assertSame('PAID', $debt->fresh()->status);
        $this->assertSame(0.0, $debt->fresh()->remaining());
    }

    public function test_debt_payment_cannot_exceed_remaining_balance(): void
    {
        $debt = app(DebtService::class)->create([
            'creditor_name' => 'Bank ABC',
            'amount' => 1000000,
            'debt_date' => now()->toDateString(),
            'due_date' => now()->addMonth()->toDateString(),
        ], $this->user);

        $this->expectException(RuntimeException::class);

        app(DebtService::class)->recordPayment($debt, [
            'payment_date' => now()->toDateString(),
            'amount' => 2000000,
            'account_id' => $this->account->id,
        ], $this->user);
    }

    public function test_receivable_payment_increases_account_balance(): void
    {
        $receivable = app(ReceivableService::class)->create([
            'debtor_name' => 'Budi',
            'amount' => 500000,
            'receivable_date' => now()->toDateString(),
            'due_date' => now()->addWeek()->toDateString(),
        ], $this->user);

        $this->assertMatchesRegularExpression('/^RCV-\d{8}-\d{6}$/', $receivable->receivable_number);

        app(ReceivableService::class)->recordPayment($receivable, [
            'payment_date' => now()->toDateString(),
            'amount' => 500000,
            'account_id' => $this->account->id,
        ], $this->user);

        $this->assertSame('10500000.00', $this->account->fresh()->current_balance);
        $this->assertSame('PAID', $receivable->fresh()->status);
    }
}
