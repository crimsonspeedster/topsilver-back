<?php
namespace App\Models;

use App\Traits\HasSeo;
use App\Traits\HasSeoBlock;
use App\Traits\HasSlug;
use App\Traits\HasTaxonomyHierarchy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Interfaces\ContentEntityInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

abstract class TaxonomyEntity extends Model implements ContentEntityInterface, HasMedia
{
    use HasFactory,
        HasSeo,
        HasSeoBlock,
        HasSlug,
        HasTaxonomyHierarchy,
        InteractsWithMedia;

    protected $fillable = [
        'title',
        'parent_id',
        'description',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id');
    }

    public function ancestors(): Collection
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current) {
            $ancestors->push($current);
            $current = $current->parent;
        }

        return $ancestors->reverse()->values();
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('media')
            ->singleFile();
    }
}
