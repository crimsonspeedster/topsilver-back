<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Collection extends TaxonomyEntity
{
    protected $fillable = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->fillable = $this->getFillable();
    }

    public function getType(): string
    {
        return 'collection';
    }

    public function products (): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_collection',
            'collection_id',
            'product_id',
        );
    }
}
