<?php

namespace App\Http\Requests;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('transaction'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $transaction = $this->route('transaction');

        $common = [
            'transaction_date' => ['required', 'date'],
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'description' => ['nullable', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        if ($transaction->transaction_type === Transaction::TYPE_TRANSFER) {
            return $common + [
                'destination_account_id' => ['required', 'integer', 'different:account_id', 'exists:accounts,id'],
            ];
        }

        return $common + [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'tag_ids' => ['array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
        ];
    }
}
