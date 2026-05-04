<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\Seo;

trait HasSeo
{
    protected static function bootHasSeo(): void
    {
        static::created(function ($model) {
            if (!$model->seo) {
                $model->seo()->create();
            }
        });

        static::deleting(function ($model) {
            $model->seo()->delete();
        });
    }

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
