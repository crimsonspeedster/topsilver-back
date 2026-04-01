<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttributeTerm extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'meta_value',
    ];

    public function attribute (): BelongsTo
    {
        return $this->belongsTo(
            Attribute::class,
            'attribute_id',
            'id',
        );
    }
}
