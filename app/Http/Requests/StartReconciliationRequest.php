<?php

namespace App\Http\Requests;

use App\Models\Reconciliation;
use Illuminate\Foundation\Http\FormRequest;

class StartReconciliationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Reconciliation::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'statement_date' => ['required', 'date'],
            'statement_balance' => ['required', 'numeric'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
