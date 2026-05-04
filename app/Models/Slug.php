<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Slug extends Model
{
    use HasFactory;
    protected $fillable = [
        'slug',
        'entity_type',
        'entity_id',
    ];

    protected static function booted(): void
    {
        static::creating(function ($slug) {
            if (empty($slug->slug)) {
                $entity = $slug->entity;

                if ($entity && isset($entity->title)) {
                    $base = Str::slug($entity->title);

                    $slug->slug = static::generateUniqueSlug($base, $slug->entity_type);
                }
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

    protected static function generateUniqueSlug($base, $entityType): string
    {
        $slug = $base;
        $i = 1;

        while (self::where('slug', $slug)
            ->where('entity_type', $entityType)
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
