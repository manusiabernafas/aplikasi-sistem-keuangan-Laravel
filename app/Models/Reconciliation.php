<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['account_id', 'statement_date', 'statement_balance', 'system_balance', 'difference', 'status', 'notes', 'created_by', 'completed_at'])]
class Reconciliation extends Model
{
    protected function casts(): array
    {
        return [
            'statement_date' => 'date',
            'statement_balance' => 'decimal:2',
            'system_balance' => 'decimal:2',
            'difference' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * @return HasMany<ReconciliationItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ReconciliationItem::class);
    }
}
