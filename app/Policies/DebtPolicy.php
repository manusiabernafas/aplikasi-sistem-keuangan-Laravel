<?php

namespace App\Policies;

use App\Models\Debt;
use App\Models\User;

class DebtPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('debt.manage');
    }

    public function view(User $user, Debt $debt): bool
    {
        return $user->hasPermission('debt.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('debt.manage');
    }

    public function update(User $user, Debt $debt): bool
    {
        return $user->hasPermission('debt.manage');
    }

    public function delete(User $user, Debt $debt): bool
    {
        return $user->hasPermission('debt.manage');
    }
}
