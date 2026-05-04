<?php
namespace App\Models;

use App\Traits\HasBasicSeo;
use App\Traits\HasPublishedAt;
use App\Traits\HasSeo;
use App\Traits\HasSeoBlock;
use App\Traits\HasSlug;
use App\Traits\HasTaxonomyHierarchy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Interfaces\TaxonomyInterface;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

abstract class Taxonomy extends Model implements TaxonomyInterface, HasMedia
{
    use HasFactory,
        HasSeo,
        HasSeoBlock,
        HasSlug,
        HasBasicSeo,
        HasTaxonomyHierarchy,
        InteractsWithMedia;

    protected $fillable = [
        'title',
        'parent_id',
        'description',
    ];

    abstract public function getType(): string;

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('main_image')
            ->singleFile()
            ->useFallbackUrl('/images/fallback-taxonomy.png');
    }
}
