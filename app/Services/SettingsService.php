<?php
namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("settings:$key", function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();

            return $setting?->value ?? $default;
        });
    }

    public function set(string $key, mixed $value, string $type = 'string'): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );

        Cache::forget("settings:$key");
    }

    public function all(): array
    {
        return Cache::rememberForever('settings:all', function () {
            return Setting::all()
                ->pluck('value', 'key')
                ->toArray();
        });
    }
}
