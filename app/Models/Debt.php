<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['debt_number', 'creditor_name', 'amount', 'debt_date', 'due_date', 'status', 'account_id', 'notes', 'created_by'])]
class Debt extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'debt_date' => 'date',
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
     * @return HasMany<DebtPayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class);
    }

    public function totalPaid(): float
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
        return max(0, (float) $this->amount - $this->totalPaid());
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
