<?php

namespace App\Models;

use App\Enums\EntityStatus;
use App\Traits\HasBasicSeo;
use App\Traits\HasPublishedAt;
use App\Traits\HasSeo;
use App\Traits\HasSeoBlock;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class FilterPage extends Model implements HasMedia
{
    use HasFactory,
        HasSlug,
        HasSeo,
        HasPublishedAt,
        HasSeoBlock,
        HasBasicSeo,
        InteractsWithMedia;

    protected $fillable = [
        'category_id',
        'title',
        'description',
        'status',
        'published_at',
    ];

    protected $casts = [
        'status' => EntityStatus::class,
        'published_at' => 'datetime',
    ];

    public function category (): BelongsTo
    {
        return $this->belongsTo(
            Category::class,
            'category_id',
            'id',
        );
    }

    public function filters(): HasMany
    {
        return $this->hasMany(
            FilterPageFilter::class,
        );
    }
}
