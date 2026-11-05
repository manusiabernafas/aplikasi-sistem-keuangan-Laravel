<?php

namespace App\Http\Requests;

use App\Models\FinancialGoal;
use Illuminate\Foundation\Http\FormRequest;

class StoreFinancialGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', FinancialGoal::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'goal_name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'numeric', 'gt:0'],
            'current_amount' => ['nullable', 'numeric', 'min:0'],
            'target_date' => ['nullable', 'date', 'after:today'],
            'description' => ['nullable', 'string', 'max:1000'],
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],
        ];
    }
}
