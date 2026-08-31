<?php

namespace App\Models;

use App\Enums\InstagramPostTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class InstagramPost extends Model implements HasMedia
{
    use InteractsWithMedia, HasFactory;

    protected $fillable = [
        'link',
        'type',
        'thumbnail_url',
        'media_url',
        'caption',
        'published_at',
        'instagram_media_id',
    ];

    protected $casts = [
        'type' => InstagramPostTypes::class,
        'published_at' => 'datetime',
    ];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('media')
            ->singleFile();

        $this
            ->addMediaCollection('video')
            ->singleFile();
    }
}
