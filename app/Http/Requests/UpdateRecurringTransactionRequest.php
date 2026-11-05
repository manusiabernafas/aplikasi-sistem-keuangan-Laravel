<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecurringTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('recurring_transaction'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'destination_account_id' => ['nullable', 'integer', 'different:account_id', 'exists:accounts,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'frequency' => ['required', 'in:DAILY,WEEKLY,MONTHLY,YEARLY'],
            'interval_count' => ['nullable', 'integer', 'min:1', 'max:365'],
            'day_of_month' => ['nullable', 'integer', 'min:1', 'max:31'],
            'day_of_week' => ['nullable', 'integer', 'min:0', 'max:6'],
            'end_date' => ['nullable', 'date'],
            'status' => ['required', 'in:ACTIVE,PAUSED,COMPLETED'],
        ];
    }
}
