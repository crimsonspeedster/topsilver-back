<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NPWarehouse extends Model
{
    protected $table = 'np_warehouses';

    protected $fillable = [
        'ref',
        'name',
        'city_ref',
        'type',
        'address',
        'is_active',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(
            NPCity::class,
            'city_ref',
            'ref'
        );
    }

    #[Scope]
    protected function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    #[Scope]
    protected function scopeSearch(Builder $query, string $value): Builder
    {
        return $query->where('name', 'like', "%{$value}%}");
    }

    #[Scope]
    protected function scopeForCity(Builder $query, string $cityRef): Builder
    {
        return $query->where('city_ref', $cityRef);
    }

    public static function findByRef (string $ref): ?self
    {
        return static::where('ref', $ref)->first();
    }

    public function markSynced (): void
    {
        $this->update([
            'last_synced_at' => now(),
        ]);
    }
}
