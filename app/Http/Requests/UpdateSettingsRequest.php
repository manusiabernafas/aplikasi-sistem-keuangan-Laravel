<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('setting.manage');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'app.name' => ['required', 'string', 'max:100'],
            'app.currency' => ['required', 'string', 'size:3'],
            'app.currency_symbol' => ['required', 'string', 'max:5'],
            'app.date_format' => ['required', 'string', 'max:20'],
            'app.timezone' => ['required', 'string', 'max:50'],
            'app.financial_year_start_month' => ['required', 'integer', 'min:1', 'max:12'],
            'app.default_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'app.notifications_enabled' => ['nullable', 'boolean'],
        ];
    }
}
