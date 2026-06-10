<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promotion extends TaxonomyEntity
{

    public function getType(): string
    {
        return 'promotion';
    }

    public function products (): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_promotion',
            'promotion_id',
            'product_id',
        );
    }
}
