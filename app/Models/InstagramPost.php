<?php

namespace App\Models;

use App\Enums\InstagramPostTypes;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class InstagramPost extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'link',
        'type',
    ];

    protected $casts = [
        'type' => InstagramPostTypes::class,
    ];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('media')
            ->singleFile();
    }
}
