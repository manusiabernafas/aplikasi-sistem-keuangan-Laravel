<?php

namespace App\Services;

use App\Models\FinancialGoal;
use App\Models\User;
use App\Support\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FinancialGoalService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        return FinancialGoal::query()
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{goal_name:string, target_amount:float, current_amount?:float, target_date?:?string, description?:?string, account_id?:?int}  $data
     */
    public function create(array $data, User $user): FinancialGoal
    {
        $goal = FinancialGoal::create([
            'goal_name' => $data['goal_name'],
            'target_amount' => $data['target_amount'],
            'current_amount' => $data['current_amount'] ?? 0,
            'target_date' => $data['target_date'] ?? null,
            'description' => $data['description'] ?? null,
            'account_id' => $data['account_id'] ?? null,
            'status' => 'IN_PROGRESS',
            'created_by' => $user->id,
        ]);

        $this->auditLog->created($goal, 'financial_goal');

        return $goal;
    }

    /**
     * @param  array{goal_name:string, target_amount:float, target_date?:?string, description?:?string, account_id?:?int}  $data
     */
    public function update(FinancialGoal $goal, array $data): FinancialGoal
    {
        $original = $goal->getOriginal();

        $goal->fill([
            'goal_name' => $data['goal_name'],
            'target_amount' => $data['target_amount'],
            'target_date' => $data['target_date'] ?? null,
            'description' => $data['description'] ?? null,
            'account_id' => $data['account_id'] ?? null,
        ])->save();

        $this->auditLog->updated($goal, 'financial_goal', $original);

        return $goal;
    }

    public function addContribution(FinancialGoal $goal, float $amount): FinancialGoal
    {
        $goal->current_amount = Money::add((string) $goal->current_amount, (string) $amount);

        if (Money::compare($goal->current_amount, $goal->target_amount) >= 0) {
            $goal->status = 'ACHIEVED';
        }

        $goal->save();

        return $goal;
    }

    public function delete(FinancialGoal $goal): void
    {
        $this->auditLog->deleted($goal, 'financial_goal');

        $goal->delete();
    }
}
