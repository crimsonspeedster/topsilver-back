<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NPCity extends Model
{
    protected $table = 'np_cities';

    protected $fillable = [
        'ref',
        'name',
        'area_ref',
        'settlement_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function warehouses(): HasMany
    {
        return $this->hasMany(
            NPWarehouse::class,
            'city_ref',
            'ref'
        );
    }

    public function area (): BelongsTo
    {
        return $this->belongsTo(
            NpArea::class,
            'area_ref',
            'ref'
        );
    }

    #[Scope]
    protected function scopeActive (Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    #[Scope]
    protected function scopeSearch (Builder $query, string $value): Builder
    {
        return $query->where('name', 'like', "%{$value}%");
    }

    #[Scope]
    protected function scopeWithWarehouses (Builder $query): Builder
    {
        return $query->where(['warehouses' => function ($q) {

            $q->where('is_active', true);

        }]);
    }

    public static function findByRef (string $ref): ?self
    {
        return static::where('ref', $ref)->first();
    }
}
