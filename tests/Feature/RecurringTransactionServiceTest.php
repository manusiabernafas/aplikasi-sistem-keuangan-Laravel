<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Category;
use App\Models\CategoryType;
use App\Models\RecurringTransaction;
use App\Models\Role;
use App\Models\User;
use App\Services\RecurringTransactionService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecurringTransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_processing_twice_on_the_same_day_does_not_duplicate_transaction(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'super-admin')->firstOrFail());

        $accountType = AccountType::create(['code' => 'BANK', 'name' => 'Bank']);
        $account = Account::create([
            'account_code' => 'BCA-01', 'account_name' => 'BCA', 'account_type_id' => $accountType->id,
            'opening_balance' => 0, 'current_balance' => 0, 'status' => 'ACTIVE',
        ]);
        $incomeType = CategoryType::create(['code' => 'INCOME', 'name' => 'Pemasukan']);
        $gaji = Category::create(['category_type_id' => $incomeType->id, 'name' => 'Gaji', 'slug' => 'gaji']);

        $recurring = RecurringTransaction::create([
            'name' => 'Gaji Bulanan',
            'transaction_type' => 'INCOME',
            'amount' => 5000000,
            'account_id' => $account->id,
            'category_id' => $gaji->id,
            'frequency' => 'MONTHLY',
            'interval_count' => 1,
            'day_of_month' => now()->day,
            'start_date' => now()->toDateString(),
            'next_run_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $service = app(RecurringTransactionService::class);

        $first = $service->processDue($admin);
        $second = $service->processDue($admin);

        $this->assertSame(1, $first['processed']);
        $this->assertSame(0, $second['processed']);
        $this->assertDatabaseCount('transactions', 1);
        $this->assertSame('5000000.00', $account->fresh()->current_balance);
        $this->assertNotEquals(now()->toDateString(), $recurring->fresh()->next_run_date->toDateString());
    }
}
