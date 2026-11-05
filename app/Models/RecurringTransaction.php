<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name', 'transaction_type', 'amount', 'account_id', 'destination_account_id', 'category_id',
    'frequency', 'interval_count', 'day_of_month', 'day_of_week', 'start_date', 'end_date',
    'next_run_date', 'last_run_date', 'status', 'created_by',
])]
class RecurringTransaction extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'next_run_date' => 'date',
            'last_run_date' => 'date',
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
     * @return BelongsTo<Account, $this>
     */
    public function destinationAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'destination_account_id');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<RecurringTransactionRun, $this>
     */
    public function runs(): HasMany
    {
        return $this->hasMany(RecurringTransactionRun::class);
    }

    public function frequencyLabel(): string
    {
        return match ($this->frequency) {
            'DAILY' => 'Harian',
            'WEEKLY' => 'Mingguan',
            'MONTHLY' => 'Bulanan',
            'YEARLY' => 'Tahunan',
            default => $this->frequency,
        };
    }
}
