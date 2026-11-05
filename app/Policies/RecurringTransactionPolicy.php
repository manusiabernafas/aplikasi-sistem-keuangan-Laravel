<?php

namespace App\Policies;

use App\Models\RecurringTransaction;
use App\Models\User;

class RecurringTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('budget.manage');
    }

    public function view(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $user->hasPermission('budget.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('budget.manage');
    }

    public function update(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $user->hasPermission('budget.manage');
    }

    public function delete(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $user->hasPermission('budget.manage');
    }
}
