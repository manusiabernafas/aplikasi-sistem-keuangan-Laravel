<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class BudgetService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function paginate(?string $status, int $perPage = 15): LengthAwarePaginator
    {
        return Budget::query()
            ->with(['category', 'account'])
            ->when($status, fn (Builder $q, $s) => $q->where('status', $s))
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{name:string, period_type:string, category_id?:?int, account_id?:?int, amount:float, alert_threshold_percent?:int, status?:string}  $data
     */
    public function create(array $data, User $user): Budget
    {
        $budget = Budget::create([
            'name' => $data['name'],
            'period_type' => $data['period_type'],
            'category_id' => $data['category_id'] ?? null,
            'account_id' => $data['account_id'] ?? null,
            'amount' => $data['amount'],
            'alert_threshold_percent' => $data['alert_threshold_percent'] ?? 80,
            'status' => $data['status'] ?? 'ACTIVE',
            'created_by' => $user->id,
        ]);

        $this->auditLog->created($budget, 'budget');

        return $budget;
    }

    /**
     * @param  array{name:string, period_type:string, category_id?:?int, account_id?:?int, amount:float, alert_threshold_percent?:int, status?:string}  $data
     */
    public function update(Budget $budget, array $data): Budget
    {
        $original = $budget->getOriginal();

        $budget->fill([
            'name' => $data['name'],
            'period_type' => $data['period_type'],
            'category_id' => $data['category_id'] ?? null,
            'account_id' => $data['account_id'] ?? null,
            'amount' => $data['amount'],
            'alert_threshold_percent' => $data['alert_threshold_percent'] ?? $budget->alert_threshold_percent,
            'status' => $data['status'] ?? $budget->status,
        ])->save();

        $this->auditLog->updated($budget, 'budget', $original);

        return $budget;
    }

    public function delete(Budget $budget): void
    {
        $this->auditLog->deleted($budget, 'budget');

        $budget->delete();
    }

    /**
     * Calculate live progress for a budget's current period and cache it into budget_items.
     *
     * @return array{planned: float, actual: float, remaining: float, percent: float, level: string}
     */
    public function progress(Budget $budget, ?CarbonInterface $forDate = null): array
    {
        $forDate = $forDate ?? now();

        [$from, $to, $periodYear, $periodMonth] = $budget->period_type === 'ANNUAL'
            ? [$forDate->copy()->startOfYear(), $forDate->copy()->endOfYear(), $forDate->year, null]
            : [$forDate->copy()->startOfMonth(), $forDate->copy()->endOfMonth(), $forDate->year, $forDate->month];

        $actual = (float) Transaction::ofType(Transaction::TYPE_EXPENSE)
            ->betweenDates($from, $to)
            ->when($budget->category_id, fn (Builder $q) => $q->where('category_id', $budget->category_id))
            ->when($budget->account_id, fn (Builder $q) => $q->where('account_id', $budget->account_id))
            ->sum('amount');

        BudgetItem::updateOrCreate(
            ['budget_id' => $budget->id, 'period_year' => $periodYear, 'period_month' => $periodMonth],
            ['planned_amount' => $budget->amount, 'actual_amount' => $actual]
        );

        $planned = (float) $budget->amount;
        $percent = $planned > 0 ? round(($actual / $planned) * 100, 1) : 0.0;

        $level = match (true) {
            $percent >= 100 => 'exceeded',
            $percent >= $budget->alert_threshold_percent => 'warning',
            default => 'ok',
        };

        return [
            'planned' => $planned,
            'actual' => $actual,
            'remaining' => max(0.0, $planned - $actual),
            'percent' => min($percent, 999),
            'level' => $level,
        ];
    }
}
