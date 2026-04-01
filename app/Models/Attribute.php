<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'type',
    ];

    public function terms (): HasMany
    {
        return $this->hasMany(
            AttributeTerm::class,
            'attribute_id',
            'id'
        );
    }
}
