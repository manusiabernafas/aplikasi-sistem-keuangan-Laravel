<?php

namespace Database\Seeders;

use App\Models\CategoryType;
use Illuminate\Database\Seeder;

class CategoryTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['code' => 'INCOME', 'name' => 'Pemasukan'],
            ['code' => 'EXPENSE', 'name' => 'Pengeluaran'],
        ] as $type) {
            CategoryType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
