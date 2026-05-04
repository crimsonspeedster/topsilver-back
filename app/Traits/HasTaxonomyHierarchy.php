<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasTaxonomyHierarchy
{
    public function children (): HasMany
    {
        return $this->hasMany(
            static::class,
            'parent_id',
        );
    }

    public function parent (): BelongsTo
    {
        return $this->belongsTo(
            static::class,
            'parent_id',
        );
    }
}
