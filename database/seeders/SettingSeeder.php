<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'app.name', 'value' => 'Keuang', 'type' => 'string', 'group' => 'general'],
            ['key' => 'app.currency', 'value' => 'IDR', 'type' => 'string', 'group' => 'general'],
            ['key' => 'app.currency_symbol', 'value' => 'Rp', 'type' => 'string', 'group' => 'general'],
            ['key' => 'app.date_format', 'value' => 'd/m/Y', 'type' => 'string', 'group' => 'general'],
            ['key' => 'app.timezone', 'value' => 'Asia/Jakarta', 'type' => 'string', 'group' => 'general'],
            ['key' => 'app.financial_year_start_month', 'value' => '1', 'type' => 'integer', 'group' => 'general'],
            ['key' => 'app.default_account_id', 'value' => null, 'type' => 'integer', 'group' => 'transaction'],
            ['key' => 'app.notifications_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'notification'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
