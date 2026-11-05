<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('budget'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'period_type' => ['required', 'in:MONTHLY,ANNUAL'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'alert_threshold_percent' => ['nullable', 'integer', 'min:1', 'max:100'],
            'status' => ['nullable', 'in:ACTIVE,INACTIVE'],
        ];
    }
}
