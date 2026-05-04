<?php
namespace App\Traits;

use App\Enums\EntityStatus;

trait HasPublishedAt
{
    protected static function bootHasPublishedAt(): void
    {
        static::saving(function ($model) {
            if ($model->status === EntityStatus::Published && !$model->published_at) {
                $model->published_at = now();
            }
            elseif ($model->status === EntityStatus::Draft) {
                $model->published_at = null;
            }
        });
    }
}
