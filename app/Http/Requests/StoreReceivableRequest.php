<?php

namespace App\Http\Requests;

use App\Models\Receivable;
use Illuminate\Foundation\Http\FormRequest;

class StoreReceivableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Receivable::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'debtor_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'receivable_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:receivable_date'],
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
