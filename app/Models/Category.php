<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends TaxonomyEntity
{
    public function getType(): string
    {
        return 'category';
    }

    public function products (): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_category',
            'category_id',
            'product_id',
        );
    }
}
