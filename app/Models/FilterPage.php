<?php

namespace App\Models;

use App\Enums\EntityStatus;
use App\Traits\HasSeo;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FilterPage extends Model
{
    use HasFactory, HasSlug, HasSeo;

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
}
