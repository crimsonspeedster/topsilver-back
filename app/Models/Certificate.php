<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'value',
        'is_used',
    ];

    protected $casts = [
        'is_used' => 'boolean',
    ];
}
