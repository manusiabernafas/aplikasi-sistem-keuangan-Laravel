<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, PermissionSeeder::class, RolePermissionSeeder::class]);
    }

    private function userWithRole(string $slug): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', $slug)->firstOrFail());

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');
    }

    public function test_staff_cannot_access_user_management(): void
    {
        $staff = $this->userWithRole('staff');

        $this->actingAs($staff)->get('/admin/users')->assertForbidden();
    }

    public function test_viewer_cannot_access_user_management(): void
    {
        $viewer = $this->userWithRole('viewer');

        $this->actingAs($viewer)->get('/admin/users')->assertForbidden();
    }

    public function test_super_admin_can_access_user_management(): void
    {
        $superAdmin = $this->userWithRole('super-admin');

        $this->actingAs($superAdmin)->get('/admin/users')->assertOk();
    }

    public function test_admin_cannot_access_user_management(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->get('/admin/users')->assertForbidden();
    }

    public function test_super_admin_can_create_a_user(): void
    {
        $superAdmin = $this->userWithRole('super-admin');
        $staffRole = Role::where('slug', 'staff')->firstOrFail();

        $response = $this->actingAs($superAdmin)->post('/admin/users', [
            'name' => 'New Staff',
            'email' => 'new.staff@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => true,
            'role_ids' => [$staffRole->id],
        ]);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', ['email' => 'new.staff@example.com']);
    }
}
