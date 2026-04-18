<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\Slug;

trait HasSlug
{
    public function sluggable(): MorphOne
    {
        return $this->morphOne(
            Slug::class,
            'entity',
            'entity_type',
            'entity_id',
            'id',
        );
    }
}
