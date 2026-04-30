<?php

namespace App\Models;

use App\Enums\CouponTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'usage_limit',
        'used_count',
        'user_usage_limit',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'usage_limit' => 'integer',
        'user_usage_limit' => 'integer',
        'used_count' => 'integer',
        'value' => 'decimal:2',
        'type' => CouponTypes::class,
    ];

    public function carts(): HasMany
    {
        return $this->hasMany(
            Cart::class,
        );
    }
}
