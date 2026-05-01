<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class SeoBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_id',
        'entity_type',
        'title',
        'excerpt',
        'content',
    ];

    public function entity (): MorphTo
    {
        return $this->morphTo(
            null,
            'entity_type',
            'entity_id',
        );
    }

    public function getExcerptResolveAttribute (): string
    {
        return $this->excerpt ?? Str::limit(strip_tags($this->content), 160);
    }
}
