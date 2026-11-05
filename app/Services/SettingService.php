<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    private const CACHE_KEY = 'keuang.settings';

    /**
     * @return Collection<string, mixed>
     */
    public function all(): Collection
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return Setting::all()->mapWithKeys(fn (Setting $setting) => [$setting->key => $this->cast($setting)]);
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()->get($key, $default);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function updateMany(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::where('key', $key)->update(['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }

    private function cast(Setting $setting): mixed
    {
        return match ($setting->type) {
            'integer' => (int) $setting->value,
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            default => $setting->value,
        };
    }
}
