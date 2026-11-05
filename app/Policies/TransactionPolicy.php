<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('transaction.view');
    }

    public function view(User $user, Transaction $transaction): bool
    {
        return $user->hasPermission('transaction.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('transaction.create');
    }

    public function update(User $user, Transaction $transaction): bool
    {
        if ($transaction->is_reconciled && ! $user->hasPermission('reconciliation.override')) {
            return false;
        }

        return $user->hasPermission('transaction.update');
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        if ($transaction->is_reconciled && ! $user->hasPermission('reconciliation.override')) {
            return false;
        }

        return $user->hasPermission('transaction.delete');
    }
}
