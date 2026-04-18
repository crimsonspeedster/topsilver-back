<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\Seo;

trait HasSeo
{
    public function seo(): MorphOne
    {
        return $this->morphOne(
            Seo::class,
            'entity',
            'entity_type',
            'entity_id',
            'id',
        );
    }
}
