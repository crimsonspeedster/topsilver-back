<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'accrual_from',
        'available_from',
        'expires_at',
    ];

    protected $casts = [
        'accrual_from' => 'datetime',
        'available_from' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    #[Scope]
    public function scopeNotExpired($query): Builder
    {
        return $query->where('expires_at', '>', now());
    }

    #[Scope]
    protected function scopeActive (Builder $query): Builder
    {
        return $query
            ->where('available_from', '<=', now());
    }

    #[Scope]
    protected function scopeFuture (Builder $query): Builder
    {
        return $query
            ->where('available_from', '>', now());
    }
}
