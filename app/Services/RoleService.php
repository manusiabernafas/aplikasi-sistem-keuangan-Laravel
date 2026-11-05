<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class RoleService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function paginate(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return Role::query()
            ->withCount('users')
            ->when($search, function ($query, string $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{name: string, description?: ?string, permission_ids?: array<int, int>}  $data
     */
    public function create(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            $role = Role::create([
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['name']),
                'description' => $data['description'] ?? null,
            ]);

            $role->permissions()->sync($data['permission_ids'] ?? []);

            $this->auditLog->created($role, 'role');

            return $role;
        });
    }

    /**
     * @param  array{name: string, description?: ?string, permission_ids?: array<int, int>}  $data
     */
    public function update(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data) {
            $original = $role->getOriginal();

            if (! $role->is_system) {
                $role->fill([
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                ])->save();
            }

            $role->permissions()->sync($data['permission_ids'] ?? []);

            $this->auditLog->updated($role, 'role', $original);

            return $role;
        });
    }

    public function delete(Role $role): void
    {
        if ($role->is_system) {
            throw new RuntimeException('Role sistem tidak dapat dihapus.');
        }

        $this->auditLog->deleted($role, 'role');

        $role->delete();
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (Role::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
