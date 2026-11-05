<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\TransactionEntry;
use App\Support\Money;

class LedgerService
{
    public function post(Transaction $transaction): void
    {
        match ($transaction->transaction_type) {
            Transaction::TYPE_INCOME => $this->postIncome($transaction),
            Transaction::TYPE_EXPENSE => $this->postExpense($transaction),
            Transaction::TYPE_TRANSFER => $this->postTransfer($transaction),
            Transaction::TYPE_ADJUSTMENT => $this->postAdjustment($transaction),
        };
    }

    /**
     * Reverse the ledger effect of a transaction by writing offsetting entries
     * and restoring account balances, without deleting the original trail.
     */
    public function reverse(Transaction $transaction): void
    {
        foreach ($transaction->entries()->get() as $entry) {
            TransactionEntry::create([
                'transaction_id' => $transaction->id,
                'account_id' => $entry->account_id,
                'category_id' => $entry->category_id,
                'debit' => $entry->credit,
                'credit' => $entry->debit,
                'entry_date' => now()->toDateString(),
                'description' => "Reversal of #{$transaction->transaction_number}",
            ]);

            if ($entry->account_id) {
                $this->adjustAccountBalance($entry->account_id, Money::sub((string) $entry->credit, (string) $entry->debit));
            }
        }
    }

    private function postIncome(Transaction $t): void
    {
        $this->createEntry($t, $t->account_id, null, $t->amount, '0');
        $this->createEntry($t, null, $t->category_id, '0', $t->amount);
        $this->adjustAccountBalance($t->account_id, (string) $t->amount);
    }

    private function postExpense(Transaction $t): void
    {
        $this->createEntry($t, null, $t->category_id, $t->amount, '0');
        $this->createEntry($t, $t->account_id, null, '0', $t->amount);
        $this->adjustAccountBalance($t->account_id, Money::negate((string) $t->amount));
    }

    private function postTransfer(Transaction $t): void
    {
        $this->createEntry($t, $t->destination_account_id, null, $t->amount, '0');
        $this->createEntry($t, $t->account_id, null, '0', $t->amount);
        $this->adjustAccountBalance($t->destination_account_id, (string) $t->amount);
        $this->adjustAccountBalance($t->account_id, Money::negate((string) $t->amount));
    }

    private function postAdjustment(Transaction $t): void
    {
        // Positive amount increases the account balance, negative amount decreases it.
        if (Money::compare((string) $t->amount, '0') >= 0) {
            $this->createEntry($t, $t->account_id, null, $t->amount, '0');
        } else {
            $this->createEntry($t, $t->account_id, null, '0', Money::negate((string) $t->amount));
        }

        $this->adjustAccountBalance($t->account_id, (string) $t->amount);
    }

    private function createEntry(Transaction $t, ?int $accountId, ?int $categoryId, string $debit, string $credit): void
    {
        TransactionEntry::create([
            'transaction_id' => $t->id,
            'account_id' => $accountId,
            'category_id' => $categoryId,
            'debit' => $debit,
            'credit' => $credit,
            'entry_date' => $t->transaction_date,
            'description' => $t->description,
        ]);
    }

    private function adjustAccountBalance(?int $accountId, string $delta): void
    {
        if (! $accountId) {
            return;
        }

        $account = Account::whereKey($accountId)->lockForUpdate()->firstOrFail();
        $account->current_balance = Money::add((string) $account->current_balance, $delta);
        $account->save();
    }
}
