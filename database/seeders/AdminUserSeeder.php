<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'superadmin@keuang.local';

        $user = User::where('email', $email)->first();

        if (! $user) {
            $password = Str::password(16);

            $user = User::create([
                'name' => 'Super Admin',
                'email' => $email,
                'password' => $password,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $this->command->warn("Super Admin dibuat — email: {$email} | password: {$password}");
            $this->command->warn('Segera login dan ganti password ini.');
        }

        $user->roles()->syncWithoutDetaching(Role::where('slug', 'super-admin')->pluck('id'));
    }
}
