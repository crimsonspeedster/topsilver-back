<?php

namespace App\Models;

use App\Enums\SeoRobotTypes;
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
        'robots',
        'keywords',
    ];

    protected $casts = [
        'robots' => SeoRobotTypes::class,
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $entity = $model->entity;

            if (!$entity) {
                return;
            }

            if (empty($model->title)) {
                $model->title = $entity->getSeoTitle();
            }

            if (empty($model->description)) {
                $model->description = $entity->getSeoDescription();
            }

            if (empty($model->robots)) {
                $model->robots = $model->resolveDefaultRobots();
            }
        });
    }

    public function entity (): MorphTo
    {
        return $this->morphTo(
            null,
            'entity_type',
            'entity_id',
        );
    }

    public function getResolvedRobotsAttribute(): SeoRobotTypes
    {
        return $this->robots ?? $this->resolveDefaultRobots();
    }

    public function resolveDefaultRobots(): SeoRobotTypes
    {
        $value = settings('seo_robots');

        if ($value) {
            return SeoRobotTypes::from($value);
        }

        return config('app.env') === 'production'
            ? SeoRobotTypes::INDEX_FOLLOW
            : SeoRobotTypes::NOINDEX_NOFOLLOW;
    }
}
