<?php

namespace App\Models;

use App\Enums\EntityStatus;
use App\Traits\HasPublishedAt;
use App\Traits\HasSeo;
use App\Traits\HasSeoBlock;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasSeo, HasSlug, HasSeoBlock, HasPublishedAt;

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

    public function getSeoTitle(): string
    {
        return $this->title;
    }

    public function getSeoDescription(): ?string
    {
        return Str::limit(strip_tags($this->content ?? ''), 160);
    }
}
