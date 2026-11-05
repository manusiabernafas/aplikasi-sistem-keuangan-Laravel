<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
        $this->user = User::factory()->create();
        $this->user->roles()->attach(Role::where('slug', 'super-admin')->firstOrFail());
    }

    public function test_cash_flow_report_renders(): void
    {
        $this->actingAs($this->user)->get('/reports/cash-flow')->assertOk();
    }

    public function test_income_report_renders(): void
    {
        $this->actingAs($this->user)->get('/reports/income')->assertOk();
    }

    public function test_net_worth_report_renders(): void
    {
        $this->actingAs($this->user)->get('/reports/net-worth')->assertOk();
    }

    public function test_summary_report_exports_excel(): void
    {
        $response = $this->actingAs($this->user)->get('/reports/summary?export=xlsx');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_cash_flow_report_exports_pdf(): void
    {
        $response = $this->actingAs($this->user)->get('/reports/cash-flow?export=pdf');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_viewer_without_report_permission_is_forbidden(): void
    {
        $staff = User::factory()->create();
        $staff->roles()->attach(Role::where('slug', 'admin')->firstOrFail());

        // Admin has report.view per matrix, so use a role without it: create ad-hoc user with no roles.
        $noRole = User::factory()->create();

        $this->actingAs($noRole)->get('/reports/cash-flow')->assertForbidden();
    }
}
