<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'cart_token',
        'subtotal',
        'total',
        'last_modified',
        'coupon_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'last_modified' => 'datetime',
    ];

    public function items (): HasMany
    {
        return $this->hasMany(
            CartItem::class,
        );
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(
            Coupon::class,
        );
    }
}
