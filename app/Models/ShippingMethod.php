<?php

namespace App\Models;

use App\Enums\ShippingMethods;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
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
        'type' => ShippingMethods::class,
        'config' => 'encrypted:array',
    ];
}
