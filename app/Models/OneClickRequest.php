<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OneClickRequest extends Model
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'total',
        'user_id',
        'name',
        'product_name',
        'product_image',
        'product_variant',
        'phone',
        'email',
        'comment',
        'status',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'status' => OrderStatus::class,
        'product_variant' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
        );
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(
            ProductVariant::class,
            'product_variant_id',
            'id'
        );
    }

    public function user (): BelongsTo
    {
        return $this->belongsTo(
            User::class,
        );
    }
}
