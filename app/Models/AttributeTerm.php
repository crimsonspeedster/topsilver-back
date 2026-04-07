<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AttributeTerm extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'meta_value',
    ];

    public function attribute (): BelongsTo
    {
        return $this->belongsTo(
            Attribute::class,
            'attribute_id',
            'id',
        );
    }

    public function products (): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'attribute_term_products',
            'attribute_term_id',
            'product_id',
        );
    }
}
