<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('user.manage');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermission('user.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('user.manage');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasPermission('user.manage');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasPermission('user.manage') && $user->isNot($model);
    }

    public function restore(User $user, User $model): bool
    {
        return $user->hasPermission('user.manage');
    }

    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
