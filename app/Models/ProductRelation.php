<?php

namespace App\Models;

use App\Enums\ProductRelationTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductRelation extends Model
{
    protected $casts = [
        'type' => ProductRelationTypes::class,
        'sort_order' => 'integer',
    ];

    protected $fillable = [
        'product_id',
        'related_product_id',
        'type',
        'sort_order',
    ];

    public function product (): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
        );
    }

    public function related (): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
            'related_product_id',
        );
    }
}
