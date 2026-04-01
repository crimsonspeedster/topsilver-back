<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Seo extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_id',
        'entity_type',
        'title',
        'description',
        'keywords',
    ];

    public function entity (): MorphTo
    {
        return $this->morphTo(
            null,
            'entity_type',
            'entity_id',
        );
    }
}
