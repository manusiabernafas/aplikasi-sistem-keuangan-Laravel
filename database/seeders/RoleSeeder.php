<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Akses penuh ke seluruh fitur sistem.'],
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Mengelola operasional keuangan sehari-hari.'],
            ['name' => 'Staff', 'slug' => 'staff', 'description' => 'Input transaksi dan data operasional.'],
            ['name' => 'Viewer', 'slug' => 'viewer', 'description' => 'Hanya dapat melihat dashboard dan laporan.'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                ['name' => $role['name'], 'description' => $role['description'], 'is_system' => true]
            );
        }
    }
}
