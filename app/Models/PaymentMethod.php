<?php

namespace App\Models;

use App\Enums\PaymentMethods;
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
}
