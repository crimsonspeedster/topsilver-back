<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    protected static function booted(): void
    {
        static::saved(function (Setting $setting) {
            Cache::forget("settings:{$setting->key}");
            Cache::forget('settings:all');
        });

        static::deleted(function (Setting $setting) {
            Cache::forget("settings:{$setting->key}");
            Cache::forget('settings:all');
        });
    }
}
