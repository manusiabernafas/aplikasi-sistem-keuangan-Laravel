<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    /**
     * @param  array{search?: ?string, role?: ?string, status?: ?string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->with('roles:id,name,slug')
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['role'] ?? null, function ($query, string $roleSlug) {
                $query->whereHas('roles', fn ($query) => $query->where('slug', $roleSlug));
            })
            ->when(($filters['status'] ?? null) !== null && $filters['status'] !== '', function ($query) use ($filters) {
                $query->where('is_active', $filters['status'] === 'active');
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{name: string, email: string, password: string, is_active?: bool, role_ids?: array<int, int>}  $data
     */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'is_active' => $data['is_active'] ?? true,
                'email_verified_at' => now(),
            ]);

            $user->roles()->sync($data['role_ids'] ?? []);

            $this->auditLog->created($user, 'user');

            return $user;
        });
    }

    /**
     * @param  array{name: string, email: string, password?: ?string, is_active?: bool, role_ids?: array<int, int>}  $data
     */
    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $original = $user->getOriginal();

            $user->fill([
                'name' => $data['name'],
                'email' => $data['email'],
                'is_active' => $data['is_active'] ?? $user->is_active,
            ]);

            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();

            $user->roles()->sync($data['role_ids'] ?? []);

            $this->auditLog->updated($user, 'user', $original);

            return $user;
        });
    }

    public function delete(User $user): void
    {
        $this->auditLog->deleted($user, 'user');

        $user->delete();
    }
}
