<?php

namespace App\Models;

use App\Enums\EntityStatus;
use App\Traits\HasSeo;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasSeo, HasSlug;

    protected $fillable = [
        'title',
        'content',
        'status',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'content' => 'array',
        'status' => EntityStatus::class,
    ];
}
