<?php

namespace App\Models;

use App\Enums\VideoTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Video extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'type',
        'url',
        'video',
    ];

    protected $casts = [
        'type' => VideoTypes::class,
    ];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('thumbnail')
            ->singleFile();
    }

    public function product (): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
        );
    }

    public function getLinkAttribute(): string
    {
        return $this->type === VideoTypes::INTERNAL ?
            Storage::disk('public')->url($this->video)
            :
            $this->url;
    }

    public function getVideoTypeAttribute(): ?string
    {
        return $this->type === VideoTypes::INTERNAL ?
            $this->getFirstMediaUrl('video')?->mime_type
            :
            null;
    }
}
