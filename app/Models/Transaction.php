<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'transaction_number', 'transaction_date', 'transaction_type', 'account_id',
    'destination_account_id', 'category_id', 'amount', 'description',
    'reference_number', 'notes', 'status', 'is_reconciled', 'created_by', 'updated_by',
])]
class Transaction extends Model
{
    use SoftDeletes;

    public const TYPE_INCOME = 'INCOME';

    public const TYPE_EXPENSE = 'EXPENSE';

    public const TYPE_TRANSFER = 'TRANSFER';

    public const TYPE_ADJUSTMENT = 'ADJUSTMENT';

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'decimal:2',
            'is_reconciled' => 'boolean',
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
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<TransactionEntry, $this>
     */
    public function entries(): HasMany
    {
        return $this->hasMany(TransactionEntry::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * @return MorphMany<Attachment, $this>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeBetweenDates(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('transaction_date', [$from, $to]);
    }

    public function badgeClass(): string
    {
        return match ($this->transaction_type) {
            self::TYPE_INCOME => 'text-bg-success-subtle text-success-emphasis',
            self::TYPE_EXPENSE => 'text-bg-danger-subtle text-danger-emphasis',
            self::TYPE_TRANSFER => 'text-bg-info-subtle text-info-emphasis',
            default => 'text-bg-secondary-subtle text-secondary-emphasis',
        };
    }

    public function typeLabel(): string
    {
        return match ($this->transaction_type) {
            self::TYPE_INCOME => 'Pemasukan',
            self::TYPE_EXPENSE => 'Pengeluaran',
            self::TYPE_TRANSFER => 'Transfer',
            default => 'Penyesuaian',
        };
    }
}
