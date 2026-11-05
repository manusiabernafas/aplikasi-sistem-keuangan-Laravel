<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDebtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('debt'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'creditor_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'debt_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:debt_date'],
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
