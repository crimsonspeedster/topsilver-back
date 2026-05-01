<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\SeoBlock;

trait HasSeoBlock
{
    public function seoBlock(): MorphOne
    {
        return $this->morphOne(
            SeoBlock::class,
            'entity',
            'entity_type',
            'entity_id',
            'id',
        );
    }
}
