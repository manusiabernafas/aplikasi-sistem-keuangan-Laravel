<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            SettingSeeder::class,
            AccountTypeSeeder::class,
            AccountSeeder::class,
            CategoryTypeSeeder::class,
            CategorySeeder::class,
            SampleTransactionSeeder::class,
        ]);
    }
}
