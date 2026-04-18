<?php

namespace App\Models;

use App\Enums\EntityStatus;
use App\Enums\ProductRelationTypes;
use App\Enums\StockStatus;
use App\Traits\HasSeo;
use App\Traits\HasSlug;
use App\Transformers\ProductAttributeTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, HasSlug, HasSeo, InteractsWithMedia;

    protected $casts = [
        'stock_status' => StockStatus::class,
        'status' => EntityStatus::class,
        'published_at' => 'datetime',
        'manage_stock' => 'boolean',
        'price' => 'decimal:2',
        'price_on_sale' => 'decimal:2',
    ];

    protected $fillable = [
        'group_key',
        'sku',
        'status',
        'title',
        'description',
        'short_description',
        'price',
        'price_on_sale',
        'manage_stock',
        'stock',
        'stock_status',
        'published_at',
    ];

    protected static function booted() : void
    {
        static::created(function ($model) {
            $model->seo()->create([
                'title' => $model->title,
                'description' => $model->description,
            ]);
        });

        static::saving(function ($model) {
            if ($model->status === EntityStatus::Published && !$model->published_at) {
                $model->published_at = now();
            }
            elseif ($model->status === EntityStatus::Draft) {
                $model->published_at = null;
            }
        });

        static::deleting(function ($model) {
            $model->seo()->delete();
            $model->sluggable()->delete();
        });
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('main_image')
            ->singleFile()
            ->useFallbackUrl('/images/fallback-product.png');

        $this
            ->addMediaCollection('gallery')
            ->useFallbackUrl('/images/fallback-product.png');
    }

    public function variants (): HasMany
    {
        return $this->hasMany(
            ProductVariant::class,
        );
    }

    public function categories (): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'product_category',
            'product_id',
            'category_id',
        );
    }

    public function bundles (): BelongsToMany
    {
        return $this->belongsToMany(
            Bundle::class,
            'bundle_items',
            'product_id',
            'bundle_id',
        );
    }

    public function collections (): BelongsToMany
    {
        return $this->belongsToMany(
            Collection::class,
            'product_collection',
            'product_id',
            'collection_id',
        );
    }

    public function labels (): BelongsToMany
    {
        return $this->belongsToMany(
            Label::class,
            'label_products',
            'product_id',
            'label_id',
        );
    }

    public function relations (): HasMany
    {
        return $this->hasMany(
            ProductRelation::class,
        );
    }

    public function crossSells(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_relations',
            'product_id',
            'related_product_id',
        )
            ->wherePivot('type', ProductRelationTypes::CROSS_SELL)
            ->withPivot('sort_order');
    }

    public function crossSellsLimited(): BelongsToMany
    {
        return $this->crossSells()
            ->orderBy('product_relations.sort_order')
            ->limit(4);
    }

    public function attributeTerms (): BelongsToMany
    {
        return $this->belongsToMany(
            AttributeTerm::class,
            'product_attribute_terms',
            'product_id',
            'attribute_term_id',
        )
            ->withPivot('is_variation');
    }

    public function groupProducts (): HasMany
    {
        return $this->hasMany(
            self::class,
            'group_key',
            'group_key'
        );
    }

    public function getVariantAttributesAttribute()
    {
        $terms = $this->attributeTerms;

        return ProductAttributeTransformer::make(
            $terms->filter(fn ($term) => $term->pivot->is_variation)
        );
    }
}
