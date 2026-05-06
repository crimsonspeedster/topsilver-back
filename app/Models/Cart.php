<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'bonuses_used',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'last_modified' => 'datetime',
        'bonuses_used' => 'integer',
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

    public function certificates(): BelongsToMany
    {
        return $this->belongsToMany(
            Certificate::class,
            'cart_certificates',
            'cart_id',
            'certificate_id'
        );
    }
}
