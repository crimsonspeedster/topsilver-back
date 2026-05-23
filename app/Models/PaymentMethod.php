<?php

namespace App\Models;

use App\Enums\PaymentMethods;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'active',
        'config',
    ];

    protected $casts = [
        'active' => 'boolean',
        'type' => PaymentMethods::class,
        'config' => 'encrypted:array',
    ];

    #[Scope]
    protected function scopeActive (Builder $query): Builder
    {
        return $query->where(
            'active',
            '=',
            true
        );
    }
}
