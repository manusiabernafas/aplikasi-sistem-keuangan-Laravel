<?php

namespace Database\Seeders;

use App\Models\AccountType;
use Illuminate\Database\Seeder;

class AccountTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['code' => 'CASH', 'name' => 'Kas'],
            ['code' => 'BANK', 'name' => 'Rekening Bank'],
            ['code' => 'E_WALLET', 'name' => 'E-Wallet'],
            ['code' => 'OTHER', 'name' => 'Lainnya'],
        ] as $type) {
            AccountType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
