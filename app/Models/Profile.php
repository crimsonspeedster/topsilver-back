<?php

namespace App\Models;

use App\Enums\SexTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'surname',
        'middle_name',
        'about',
        'sex',
        'dob',
        'city_id',
    ];

    protected $casts = [
        'dob' => 'date',
        'sex' => SexTypes::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
        );
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(
            City::class,
        );
    }
}
