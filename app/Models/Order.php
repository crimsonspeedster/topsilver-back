<?php

namespace App\Models;

use App\Enums\CouponTypes;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethods;
use App\Enums\ShippingMethods;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'subtotal',
        'total',
        'paid_at',
        'notes',
        'first_name',
        'last_name',
        'middle_name',
        'phone',
        'email',
        'payment_type',
        'payment_data',
        'shipping_type',
        'coupon_code',
        'coupon_type',
        'coupon_value',
        'discount_amount',
        'shipping_data',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'payment_type' => PaymentMethods::class,
        'shipping_type' => ShippingMethods::class,
        'shipping_data' => 'array',
        'payment_data' => 'array',
        'paid_at' => 'datetime',
        'total' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'coupon_value' => 'decimal:2',
        'coupon_type' => CouponTypes::class,
    ];

    public static function booted(): void
    {
        static::saving(function ($model) {
            $subtotal = $model->subtotal ?? 0;
            $discount = $model->discount_amount ?? 0;

            $model->total = max(0, $subtotal - $discount);
        });
    }

    public function user (): BelongsTo
    {
        return $this->belongsTo(
            User::class,
        );
    }

    public function items (): HasMany
    {
        return $this->hasMany(
            OrderItem::class,
        );
    }
}
