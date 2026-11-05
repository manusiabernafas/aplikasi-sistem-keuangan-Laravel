<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'superadmin@keuang.local')->first();

        $accounts = [
            ['account_code' => 'CASH-001', 'account_name' => 'Kas', 'type' => 'CASH', 'opening_balance' => 1000000],
            ['account_code' => 'BCA-001', 'account_name' => 'BCA', 'type' => 'BANK', 'opening_balance' => 5000000],
            ['account_code' => 'MANDIRI-001', 'account_name' => 'Mandiri', 'type' => 'BANK', 'opening_balance' => 2500000],
            ['account_code' => 'BRI-001', 'account_name' => 'BRI', 'type' => 'BANK', 'opening_balance' => 1500000],
            ['account_code' => 'GOPAY-001', 'account_name' => 'GoPay', 'type' => 'E_WALLET', 'opening_balance' => 250000],
            ['account_code' => 'OVO-001', 'account_name' => 'OVO', 'type' => 'E_WALLET', 'opening_balance' => 150000],
            ['account_code' => 'DANA-001', 'account_name' => 'Dana', 'type' => 'E_WALLET', 'opening_balance' => 200000],
        ];

        foreach ($accounts as $account) {
            $type = AccountType::where('code', $account['type'])->first();

            Account::updateOrCreate(
                ['account_code' => $account['account_code']],
                [
                    'account_name' => $account['account_name'],
                    'account_type_id' => $type->id,
                    'opening_balance' => $account['opening_balance'],
                    'current_balance' => $account['opening_balance'],
                    'currency' => 'IDR',
                    'status' => 'ACTIVE',
                    'created_by' => $admin?->id,
                    'updated_by' => $admin?->id,
                ]
            );
        }
    }
}
