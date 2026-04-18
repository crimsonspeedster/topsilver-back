<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bundle extends Model
{
    use HasFactory;

    protected $casts = [
        'active' => 'boolean',
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
    ];

    protected $fillable = [
        'sku',
        'title',
        'price',
        'old_price',
        'active',
    ];

    public function items (): HasMany
    {
        return $this->hasMany(
            BundleItem::class,
        );
    }

    public function products (): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'bundle_items',
            'bundle_id',
            'product_id'
        )->withPivot('quantity');
    }

    #[Scope]
    protected function scopeForProduct (Builder $query, int $productId): Builder
    {
        return $query->whereHas('items', function ($q) use ($productId) {
            $q->where('product_id', $productId);
        });
    }
}
