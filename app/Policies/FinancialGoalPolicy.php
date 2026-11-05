<?php

namespace App\Policies;

use App\Models\FinancialGoal;
use App\Models\User;

class FinancialGoalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('budget.manage');
    }

    public function view(User $user, FinancialGoal $financialGoal): bool
    {
        return $user->hasPermission('budget.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('budget.manage');
    }

    public function update(User $user, FinancialGoal $financialGoal): bool
    {
        return $user->hasPermission('budget.manage');
    }

    public function delete(User $user, FinancialGoal $financialGoal): bool
    {
        return $user->hasPermission('budget.manage');
    }
}
