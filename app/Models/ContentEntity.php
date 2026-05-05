<?php
namespace App\Models;

use App\Enums\EntityStatus;
use App\Interfaces\ContentEntityInterface;
use App\Traits\HasSeo;
use App\Traits\HasSeoBlock;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

abstract class ContentEntity extends Model implements HasMedia, ContentEntityInterface
{
    use HasFactory,
        HasSeo,
        HasSeoBlock,
        HasSlug,
        InteractsWithMedia;

    protected $casts = [
        'published_at' => 'datetime',
        'content' => 'array',
        'status' => EntityStatus::class,
    ];

    protected $fillable = [
        'title',
        'short_description',
        'content',
        'status',
        'published_at',
    ];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('media')
            ->singleFile();
    }
}
