<?php
namespace App\Models;

use App\Traits\HasSeo;
use App\Traits\HasSeoBlock;
use App\Traits\HasSlug;
use App\Traits\HasTaxonomyHierarchy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Interfaces\ContentEntityInterface;
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

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('media')
            ->singleFile();
    }
}
