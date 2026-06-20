<?php

namespace App\Models;

use App\Enums\LabelTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Label extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'external_id',
    ];

    protected $casts = [
        'type' => LabelTypes::class,
    ];

    public function products (): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'label_products',
            'label_id',
            'product_id',
        );
    }
}
