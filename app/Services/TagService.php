<?php

namespace App\Services;

use App\Models\Tag;
use Illuminate\Support\Str;
use RuntimeException;

class TagService
{
    /**
     * @param  array{name:string, color?:?string}  $data
     */
    public function create(array $data): Tag
    {
        return Tag::create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'color' => $data['color'] ?? null,
        ]);
    }

    /**
     * @param  array{name:string, color?:?string}  $data
     */
    public function update(Tag $tag, array $data): Tag
    {
        $tag->fill([
            'name' => $data['name'],
            'color' => $data['color'] ?? null,
        ])->save();

        return $tag;
    }

    public function delete(Tag $tag): void
    {
        if ($tag->transactions()->exists()) {
            throw new RuntimeException('Tag tidak dapat dihapus karena masih dipakai transaksi.');
        }

        $tag->delete();
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (Tag::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
