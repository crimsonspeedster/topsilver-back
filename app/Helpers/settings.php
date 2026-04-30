<?php
use App\Services\SettingsService;

if (!function_exists('settings')) {
    function settings(?string $key = null, mixed $default = null): mixed
    {
        $service = app(SettingsService::class);

        if ($key === null) {
            return $service->all();
        }

        return $service->get($key, $default);
    }
}
