<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class CategoryService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    /**
     * @return Collection<int, Category>
     */
    public function treeByType(string $typeCode)
    {
        return Category::query()
            ->ofType($typeCode)
            ->topLevel()
            ->with(['children' => fn ($q) => $q->orderBy('sort_order')->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array{category_type_id:int, parent_id?:?int, name:string, icon?:?string, color?:?string, is_active?:bool, sort_order?:int}  $data
     */
    public function create(array $data): Category
    {
        $this->assertUniqueName($data['category_type_id'], $data['parent_id'] ?? null, $data['name']);

        $category = Category::create([
            'category_type_id' => $data['category_type_id'],
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::random(4),
            'icon' => $data['icon'] ?? null,
            'color' => $data['color'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        $this->auditLog->created($category, 'category');

        return $category;
    }

    /**
     * @param  array{category_type_id:int, parent_id?:?int, name:string, icon?:?string, color?:?string, is_active?:bool, sort_order?:int}  $data
     */
    public function update(Category $category, array $data): Category
    {
        if (($data['parent_id'] ?? null) === $category->id) {
            throw new RuntimeException('Kategori tidak dapat menjadi induk dirinya sendiri.');
        }

        $this->assertUniqueName($data['category_type_id'], $data['parent_id'] ?? null, $data['name'], $category->id);

        $original = $category->getOriginal();

        $category->fill([
            'category_type_id' => $data['category_type_id'],
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'icon' => $data['icon'] ?? null,
            'color' => $data['color'] ?? null,
            'is_active' => $data['is_active'] ?? $category->is_active,
            'sort_order' => $data['sort_order'] ?? $category->sort_order,
        ])->save();

        $this->auditLog->updated($category, 'category', $original);

        return $category;
    }

    public function delete(Category $category): void
    {
        if ($category->children()->exists()) {
            throw new RuntimeException('Kategori tidak dapat dihapus karena memiliki subkategori.');
        }

        if ($category->transactions()->exists()) {
            throw new RuntimeException('Kategori tidak dapat dihapus karena sudah dipakai transaksi.');
        }

        $this->auditLog->deleted($category, 'category');

        $category->delete();
    }

    private function assertUniqueName(int $typeId, ?int $parentId, string $name, ?int $ignoreId = null): void
    {
        $exists = Category::where('category_type_id', $typeId)
            ->where('parent_id', $parentId)
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists();

        if ($exists) {
            throw new RuntimeException('Nama kategori sudah dipakai pada level yang sama.');
        }
    }
}
