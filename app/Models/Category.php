<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'parent_id',
    ];

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

    public function products (): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_category',
            'category_id',
            'product_id',
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
}
