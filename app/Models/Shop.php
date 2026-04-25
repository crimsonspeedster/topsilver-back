<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city_id',
        'address',
        'address_link',
        'phone',
        'time_working'
    ];

    public function city (): BelongsTo
    {
        return $this->belongsTo(
            City::class,
        );
    }
}
