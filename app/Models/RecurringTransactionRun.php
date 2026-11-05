<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['recurring_transaction_id', 'run_date', 'transaction_id', 'status', 'error_message'])]
class RecurringTransactionRun extends Model
{
    protected function casts(): array
    {
        return [
            'run_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<RecurringTransaction, $this>
     */
    public function recurringTransaction(): BelongsTo
    {
        return $this->belongsTo(RecurringTransaction::class);
    }

    /**
     * @return BelongsTo<Transaction, $this>
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
