<?php

namespace App\Models;

use App\Services\ContentResolver;
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
        'content',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function entity (): MorphTo
    {
        return $this->morphTo(
            null,
            'entity_type',
            'entity_id',
        );
    }

    public function getBlocksAttribute(): array
    {
        return app(ContentResolver::class)->resolve($this->content);
    }
}
