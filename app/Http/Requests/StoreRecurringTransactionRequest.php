<?php

namespace App\Http\Requests;

use App\Models\RecurringTransaction;
use Illuminate\Foundation\Http\FormRequest;

class StoreRecurringTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', RecurringTransaction::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'transaction_type' => ['required', 'in:INCOME,EXPENSE,TRANSFER'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'destination_account_id' => ['nullable', 'integer', 'different:account_id', 'exists:accounts,id', 'required_if:transaction_type,TRANSFER'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id', 'required_unless:transaction_type,TRANSFER'],
            'frequency' => ['required', 'in:DAILY,WEEKLY,MONTHLY,YEARLY'],
            'interval_count' => ['nullable', 'integer', 'min:1', 'max:365'],
            'day_of_month' => ['nullable', 'integer', 'min:1', 'max:31'],
            'day_of_week' => ['nullable', 'integer', 'min:0', 'max:6'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}
