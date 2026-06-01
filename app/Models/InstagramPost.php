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
    ];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('media')
            ->singleFile();
    }
}
