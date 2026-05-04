<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\Slug;

trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::created(function ($model) {
            if (!$model->sluggable) {
                $model->sluggable()->create();
            }
        });

        static::deleting(function ($model) {
            $model->sluggable()->delete();
        });
    }

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
