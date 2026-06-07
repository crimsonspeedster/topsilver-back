<?php
namespace App\Models;

use App\Enums\EntityStatus;
use App\Interfaces\ContentEntityInterface;
use App\Services\ContentResolver;
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

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('media')
            ->singleFile();

        $this
            ->addMediaCollection('banner')
            ->singleFile();
    }

    protected function baseFillable(): array
    {
        return [
            'title',
            'short_description',
            'content',
            'status',
            'published_at',
        ];
    }

    public function getFillable(): array
    {
        return $this->baseFillable();
    }

    public function getBlocksAttribute(): array
    {
        return app(ContentResolver::class)->resolve($this->content);
    }

    public function getIsHomePageAttribute(): bool
    {
        $home_page_relation = settings('home_page');

        if (!$home_page_relation) return false;

        return $this->id === (int) $home_page_relation;
    }
}
