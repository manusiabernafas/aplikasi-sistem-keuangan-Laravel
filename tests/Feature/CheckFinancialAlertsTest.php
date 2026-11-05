<?php

namespace Tests\Feature;

use App\Models\Debt;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CheckFinancialAlertsTest extends TestCase
{
    use RefreshDatabase;

    public function test_overdue_debt_flips_status_and_notifies_admin(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'super-admin')->firstOrFail());

        $debt = Debt::create([
            'debt_number' => 'DBT-20260101-000001',
            'creditor_name' => 'Bank ABC',
            'amount' => 1000000,
            'debt_date' => now()->subMonth()->toDateString(),
            'due_date' => now()->subDay()->toDateString(),
            'status' => 'OUTSTANDING',
        ]);

        Artisan::call('keuang:check-alerts');

        $this->assertSame('OVERDUE', $debt->fresh()->status);
        $this->assertSame(1, $admin->fresh()->unreadNotifications()->count());
    }

    public function test_running_alerts_twice_in_one_day_does_not_duplicate_notification(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'super-admin')->firstOrFail());

        Debt::create([
            'debt_number' => 'DBT-20260101-000002',
            'creditor_name' => 'Bank XYZ',
            'amount' => 1000000,
            'debt_date' => now()->subMonth()->toDateString(),
            'due_date' => now()->subDay()->toDateString(),
            'status' => 'OUTSTANDING',
        ]);

        Artisan::call('keuang:check-alerts');
        Artisan::call('keuang:check-alerts');

        $this->assertSame(1, $admin->fresh()->notifications()->count());
    }
}
