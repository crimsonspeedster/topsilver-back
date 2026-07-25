<?php

namespace App\Models;

use App\Enums\PromotionTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promotion extends TaxonomyEntity
{
    public function getType(): string
    {
        return 'promotion';
    }

    protected function extraFillable(): array
    {
        return [
            'type',
            'message_for_cart',
        ];
    }

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'type' => PromotionTypes::class,
        ]);
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
