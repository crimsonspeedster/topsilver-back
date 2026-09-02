<?php

namespace App\Models;

use App\Enums\MenuItemEntityTypes;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'html_block_id',
        'title',
        'type',
        'url',
        'entity_id',
        'entity_type',
        'use_html_blocks',
        'order'
    ];

    protected $casts = [
        'order' => 'integer',
        'type' => MenuItemEntityTypes::class,
        'use_html_blocks' => 'boolean',
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

    public function htmlBlock(): BelongsTo
    {
        return $this->belongsTo(
            HTMLBlock::class,
            'html_block_id',
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

    #[Scope]
    protected function scopeParents(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function getLinkAttribute(): string
    {
        return match ($this->type) {
            MenuItemEntityTypes::CUSTOM => $this->url,
            MenuItemEntityTypes::ENTITY => $this->getEntitySlug(),
        };
    }

    private function getEntitySlug(): string
    {
        return $this->entity?->sluggable?->slug ?? '';
    }
}
