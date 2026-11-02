<?php

namespace App\Models;

use App\Enums\EntityStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Shop extends ContentEntity
{
    protected function extraFillable(): array
    {
        return [
            'city_id',
            'address',
            'address_link',
            'phone',
            'time_working',
            'external_id',
        ];
    }

    public function city (): BelongsTo
    {
        return $this->belongsTo(
            City::class,
        );
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'shop_product',
        );
    }

    public function getType(): string
    {
        return 'shop';
    }

    #[Scope]
    protected function scopePublished (Builder $query): Builder
    {
        return $query->where(
            'status',
            '=',
            EntityStatus::Published
        );
    }
}
