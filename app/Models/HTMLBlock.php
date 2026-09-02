<?php

namespace App\Models;

use App\Services\ContentResolver;
use Illuminate\Database\Eloquent\Model;

class HTMLBlock extends Model
{
    protected $table = 'html_blocks';

    protected $fillable = [
        'title',
        'content',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function getBlocksAttribute(): array
    {
        return app(ContentResolver::class)->resolve($this->content);
    }
}
