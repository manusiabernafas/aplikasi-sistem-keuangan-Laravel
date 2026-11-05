<?php

namespace App\Policies;

use App\Models\Reconciliation;
use App\Models\User;

class ReconciliationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('reconciliation.manage');
    }

    public function view(User $user, Reconciliation $reconciliation): bool
    {
        return $user->hasPermission('reconciliation.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('reconciliation.manage');
    }

    public function update(User $user, Reconciliation $reconciliation): bool
    {
        return $user->hasPermission('reconciliation.manage');
    }
}
