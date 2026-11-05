<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['goal_name', 'target_amount', 'current_amount', 'target_date', 'description', 'account_id', 'status', 'created_by'])]
class FinancialGoal extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'target_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function progressPercent(): float
    {
        if ((float) $this->target_amount <= 0) {
            return 0;
        }

        return min(100, round(((float) $this->current_amount / (float) $this->target_amount) * 100, 1));
    }
}
