<?php

namespace App\Policies;

use App\Models\Receivable;
use App\Models\User;

class ReceivablePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('receivable.manage');
    }

    public function view(User $user, Receivable $receivable): bool
    {
        return $user->hasPermission('receivable.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('receivable.manage');
    }

    public function update(User $user, Receivable $receivable): bool
    {
        return $user->hasPermission('receivable.manage');
    }

    public function delete(User $user, Receivable $receivable): bool
    {
        return $user->hasPermission('receivable.manage');
    }
}
