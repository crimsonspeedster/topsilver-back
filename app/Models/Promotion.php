<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promotion extends TaxonomyEntity
{
    protected $fillable = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->fillable = $this->getFillable();
    }

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
