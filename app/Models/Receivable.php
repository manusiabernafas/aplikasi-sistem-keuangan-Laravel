<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['receivable_number', 'debtor_name', 'amount', 'receivable_date', 'due_date', 'status', 'account_id', 'notes', 'created_by'])]
class Receivable extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'receivable_date' => 'date',
            'due_date' => 'date',
            'amount' => 'decimal:2',
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
     * @return HasMany<ReceivablePayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(ReceivablePayment::class);
    }

    public function totalReceived(): float
    {
        if ($this->relationLoaded('payments')) {
            return (float) $this->payments->sum('amount');
        }

        if (array_key_exists('payments_sum_amount', $this->attributes)) {
            return (float) $this->attributes['payments_sum_amount'];
        }

        return (float) $this->payments()->sum('amount');
    }

    public function remaining(): float
    {
        return max(0, (float) $this->amount - $this->totalReceived());
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'PAID' => 'text-bg-success-subtle text-success-emphasis',
            'PARTIALLY_PAID' => 'text-bg-info-subtle text-info-emphasis',
            'OVERDUE' => 'text-bg-danger-subtle text-danger-emphasis',
            default => 'text-bg-secondary-subtle text-secondary-emphasis',
        };
    }
}
