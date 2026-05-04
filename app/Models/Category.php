<?php

namespace App\Models;

use App\Interfaces\TaxonomyInterface;
use App\Traits\HasSeo;
use App\Traits\HasSeoBlock;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Category extends Model implements TaxonomyInterface, HasMedia
{
    use HasFactory, HasSlug, HasSeo, HasSeoBlock, InteractsWithMedia;

    protected $fillable = [
        'title',
        'parent_id',
        'description',
    ];

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

    public function children (): HasMany
    {
        return $this->hasMany(
            self::class,
            'parent_id',
        );
    }

    public function parent (): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'parent_id',
        );
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('main_image')
            ->singleFile()
            ->useFallbackUrl('/images/fallback-taxonomy.png');
    }
}
