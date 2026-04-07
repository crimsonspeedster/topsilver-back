<?php

namespace App\Models;

use App\Enums\EntityStatus;
use App\Enums\ProductTypes;
use App\Enums\StockStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Product extends Model
{
    use HasFactory;

    protected $casts = [
        'stock_status' => StockStatus::class,
        'type' => ProductTypes::class,
        'status' => EntityStatus::class,
        'published_at' => 'datetime',
    ];

    protected $fillable = [
        'parent_id',
        'external_id',
        'sku',
        'type',
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

    public function sluggable (): MorphOne
    {
        return $this->morphOne(
            Slug::class,
            'entity',
            'entity_type',
            'entity_id',
            'id',
        );
    }

    public function seo (): MorphOne
    {
        return $this->morphOne(
            Seo::class,
            'entity',
            'entity_type',
            'entity_id',
            'id',
        );
    }

    public function parent (): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
            'parent_id',
            'id',
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

    public function attributeTerms (): BelongsToMany
    {
        return $this->belongsToMany(
            AttributeTerm::class,
            'attribute_term_products',
            'product_id',
            'attribute_term_id'
        );
    }

    public function variations (): HasMany
    {
        return $this->hasMany(
            Product::class,
            'parent_id',
            'id'
        )
            ->where('type', '=', ProductTypes::Variation);
    }

//    #[Scope]
//    protected function scopePublished (Builder $query)
//    {
//        return $query->where(function ($q) {
//            $q->where('type', '!=', ProductTypes::Variable)
//                ->where('status', '=', EntityStatus::Published);
//        });
//    }
}
