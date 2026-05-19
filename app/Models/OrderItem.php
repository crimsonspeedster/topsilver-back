<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'entity_id',
        'entity_type',
        'entity_name',
        'entity_image',
        'entity_price',
        'product_variant',
        'quantity',
        'total',
    ];

    protected $casts = [
        'product_variant' => 'array',
        'total' => 'decimal:2',
    ];

    public function order (): BelongsTo
    {
        return $this->belongsTo(
            Order::class,
        );
    }

    public function entity (): MorphTo
    {
        return $this->morphTo(
            null,
            'entity_type',
            'entity_id',
        );
    }
}
