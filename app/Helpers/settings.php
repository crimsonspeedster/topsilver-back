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

if (! function_exists('frontend_url')) {
    function frontend_url(string $path = ''): string
    {
        $base = rtrim(config('app.frontend_url'), '/');

        $path = ltrim($path, '/');

        return $path
            ? "{$base}/{$path}"
            : $base;
    }
}
