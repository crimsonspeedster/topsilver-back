<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shop extends ContentEntity
{

    protected $fillable = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->fillable = array_merge(parent::getFillable(), [
            'city_id',
            'address',
            'address_link',
            'phone',
            'time_working'
        ]);
    }

    public function city (): BelongsTo
    {
        return $this->belongsTo(
            City::class,
        );
    }

    public function getType(): string
    {
        return 'shop';
    }
}
