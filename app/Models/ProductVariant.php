<?php

namespace App\Models;

use App\Enums\StockStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $casts = [
        'stock_status' => StockStatus::class,
        'price' => 'decimal:2',
        'price_on_sale' => 'decimal:2',
        'stock' => 'integer',
    ];

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'price_on_sale',
        'stock',
        'stock_status',
        'variant_key',
    ];

    public function product (): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
        );
    }

    public function attributeTerms (): BelongsToMany
    {
        return $this->belongsToMany(
            AttributeTerm::class,
            'attribute_term_variants',
            'product_variant_id',
            'attribute_term_id'
        );
    }
}
