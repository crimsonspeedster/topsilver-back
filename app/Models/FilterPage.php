<?php

namespace App\Models;

use App\Enums\EntityStatus;
use App\Traits\HasPublishedAt;
use App\Traits\HasSeo;
use App\Traits\HasSeoBlock;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class FilterPage extends Model
{
    use HasFactory, HasSlug, HasSeo, HasPublishedAt, HasSeoBlock;

    protected $fillable = [
        'category_id',
        'title',
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

    public function getSeoTitle(): string
    {
        return $this->title;
    }
}
