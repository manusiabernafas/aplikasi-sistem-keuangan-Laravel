<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $all = array_keys(array_merge(...array_values(PermissionSeeder::definitions())));

        $matrix = [
            'super-admin' => $all,
            'admin' => array_diff($all, ['user.manage', 'setting.manage', 'backup.manage', 'reconciliation.override']),
            'staff' => [
                'dashboard.view',
                'transaction.view', 'transaction.create', 'transaction.update',
                'budget.manage', 'debt.manage', 'receivable.manage',
                'report.view',
            ],
            'viewer' => ['dashboard.view', 'report.view'],
        ];

        foreach ($matrix as $roleSlug => $permissionSlugs) {
            $role = Role::where('slug', $roleSlug)->first();

            if (! $role) {
                continue;
            }

            $permissionIds = Permission::whereIn('slug', $permissionSlugs)->pluck('id');

            $role->permissions()->sync($permissionIds);
        }
    }
}
