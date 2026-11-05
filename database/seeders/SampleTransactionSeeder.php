<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Category;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Database\Seeder;

class SampleTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'superadmin@keuang.local')->first();

        if (! $admin) {
            return;
        }

        $bca = Account::where('account_code', 'BCA-001')->first();
        $kas = Account::where('account_code', 'CASH-001')->first();
        $gopay = Account::where('account_code', 'GOPAY-001')->first();

        $gaji = Category::where('name', 'Gaji')->whereHas('categoryType', fn ($q) => $q->where('code', 'INCOME'))->first();
        $makanan = Category::where('name', 'Makanan')->whereHas('categoryType', fn ($q) => $q->where('code', 'EXPENSE'))->first();
        $transportasi = Category::where('name', 'BBM')->whereHas('categoryType', fn ($q) => $q->where('code', 'EXPENSE'))->first();

        if (! $bca || ! $gaji) {
            return;
        }

        /** @var TransactionService $service */
        $service = app(TransactionService::class);

        $service->createIncome([
            'transaction_date' => now()->startOfMonth()->addDays(4)->toDateString(),
            'account_id' => $bca->id,
            'category_id' => $gaji->id,
            'amount' => 8000000,
            'description' => 'Gaji bulanan',
        ], $admin);

        if ($makanan) {
            $service->createExpense([
                'transaction_date' => now()->startOfMonth()->addDays(6)->toDateString(),
                'account_id' => $bca->id,
                'category_id' => $makanan->id,
                'amount' => 75000,
                'description' => 'Makan siang',
            ], $admin);
        }

        if ($transportasi && $kas) {
            $service->createExpense([
                'transaction_date' => now()->startOfMonth()->addDays(7)->toDateString(),
                'account_id' => $kas->id,
                'category_id' => $transportasi->id,
                'amount' => 100000,
                'description' => 'Isi bensin',
            ], $admin);
        }

        if ($gopay) {
            $service->createTransfer([
                'transaction_date' => now()->startOfMonth()->addDays(8)->toDateString(),
                'account_id' => $bca->id,
                'destination_account_id' => $gopay->id,
                'amount' => 200000,
                'description' => 'Top up GoPay',
            ], $admin);
        }
    }
}
