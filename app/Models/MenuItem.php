<?php

namespace App\Models;

use App\Enums\MenuItemTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'type',
        'url',
        'entity_id',
        'entity_type',
        'order'
    ];

    protected $casts = [
        'order' => 'integer',
        'type' => MenuItemTypes::class,
    ];

    public function entity (): MorphTo
    {
        return $this->morphTo(
            null,
            'entity_type',
            'entity_id',
        );
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(
            Menu::class,
        );
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            MenuItem::class,
            'parent_id',
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            MenuItem::class,
            'parent_id',
        )
            ->orderBy('order');
    }

    public function getLinkAttribute(): string
    {
        return match ($this->type) {
            MenuItemTypes::CUSTOM => $this->url,
            MenuItemTypes::ENTITY => $this->getEntitySlug(),
        };
    }

    private function getEntitySlug(): string
    {
        return $this->entity?->sluggable?->slug ?? '';
    }
}
