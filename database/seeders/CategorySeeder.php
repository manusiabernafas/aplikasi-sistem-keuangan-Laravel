<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $incomeType = CategoryType::where('code', 'INCOME')->firstOrFail();
        $expenseType = CategoryType::where('code', 'EXPENSE')->firstOrFail();

        foreach (['Gaji', 'Bonus', 'Penjualan', 'Komisi', 'Investasi', 'Hadiah', 'Pendapatan Lainnya'] as $index => $name) {
            $this->upsert($incomeType->id, null, $name, $index);
        }

        $expenseFlat = ['Makanan', 'Tagihan', 'Belanja', 'Hiburan', 'Pendidikan', 'Kesehatan', 'Cicilan', 'Operasional', 'Lainnya'];
        foreach ($expenseFlat as $index => $name) {
            $this->upsert($expenseType->id, null, $name, $index);
        }

        $kebutuhanRumah = $this->upsert($expenseType->id, null, 'Kebutuhan Rumah', count($expenseFlat));
        foreach (['Listrik', 'Air', 'Internet', 'Gas'] as $index => $name) {
            $this->upsert($expenseType->id, $kebutuhanRumah->id, $name, $index);
        }

        $transportasi = $this->upsert($expenseType->id, null, 'Transportasi', count($expenseFlat) + 1);
        foreach (['BBM', 'Parkir', 'Tol', 'Servis'] as $index => $name) {
            $this->upsert($expenseType->id, $transportasi->id, $name, $index);
        }
    }

    private function upsert(int $typeId, ?int $parentId, string $name, int $sortOrder): Category
    {
        return Category::updateOrCreate(
            ['category_type_id' => $typeId, 'parent_id' => $parentId, 'name' => $name],
            ['slug' => Str::slug($name).'-'.Str::random(4), 'sort_order' => $sortOrder, 'is_active' => true]
        );
    }
}
