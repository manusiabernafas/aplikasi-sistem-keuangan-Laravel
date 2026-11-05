<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('user.manage');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasPermission('user.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('user.manage');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasPermission('user.manage');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermission('user.manage') && ! $role->is_system;
    }

    public function restore(User $user, Role $role): bool
    {
        return $user->hasPermission('user.manage');
    }

    public function forceDelete(User $user, Role $role): bool
    {
        return false;
    }
}
