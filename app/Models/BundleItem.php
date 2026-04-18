<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BundleItem extends Model
{
    use HasFactory;

    protected $casts = [
        'quantity' => 'integer',
    ];

    protected $fillable = [
        'product_id',
        'bundle_id',
        'quantity',
    ];

    public function bundle (): BelongsTo
    {
        return $this->belongsTo(
            Bundle::class,
        );
    }

    public function product (): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
        );
    }
}
