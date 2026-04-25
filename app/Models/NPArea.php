<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NPArea extends Model
{
    protected $table = 'np_areas';

    protected $fillable = [
        'ref',
        'name',
    ];

    public function cities (): HasMany
    {
        return $this->hasMany(
            NPCity::class,
            'area_ref',
            'ref'
        );
    }

    #[Scope]
    protected function scopeSearch (Builder $query, string $value): Builder
    {
        return $query->where('name', 'like', "%{$value}%");
    }

    public static function findByRef (string $ref): ?self
    {
        return static::where('ref', $ref)->first();
    }
}
