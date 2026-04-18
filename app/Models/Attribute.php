<?php

namespace App\Models;

use App\Enums\AttributeTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    use HasFactory;

    protected $casts = [
        'type' => AttributeTypes::class,
    ];

    protected $fillable = [
        'title',
        'slug',
        'type',
    ];

    public function terms (): HasMany
    {
        return $this->hasMany(
            AttributeTerm::class,
            'attribute_id',
            'id'
        );
    }
}
