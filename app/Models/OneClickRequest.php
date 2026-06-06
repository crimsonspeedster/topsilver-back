<?php

namespace App\Models;

use App\Enums\OneClickOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OneClickRequest extends Model
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'name',
        'phone',
        'email',
        'comment',
        'status',
    ];

    protected $casts = [
        'status' => OneClickOrderStatus::class,
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
        );
    }
}
