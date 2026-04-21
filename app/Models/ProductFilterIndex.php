<?php

namespace App\Models;

use App\Enums\StockStatus;
use Illuminate\Database\Eloquent\Model;

class ProductFilterIndex extends Model
{
    protected $table = 'product_filter_index';

    protected $casts = [
        'is_variant' => 'boolean',
        'stock_status' => StockStatus::class,
        'price' => 'decimal:2',
    ];

    protected $fillable = [
        'product_id',
        'category_id',
        'collection_id',
        'attribute_id',
        'attribute_term_id',
        'is_variant',
        'stock_status',
        'price',
    ];
}
