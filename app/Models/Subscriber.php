<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Subscriber extends Model
{
    protected $fillable = [
        'email',
        'is_active',
        'unsubscribe_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (!$model->unsubscribe_token) {
                $model->unsubscribe_token = (string) Str::uuid();
            }
        });
    }
}
