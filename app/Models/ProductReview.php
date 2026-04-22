<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'parent_id',
        'comment',
        'rating',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
        'status'  => ReviewStatus::class,
    ];

    public function product (): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
        );
    }

    public function user (): BelongsTo
    {
        return $this->belongsTo(
            User::class,
        );
    }

    public function order (): BelongsTo
    {
        return $this->belongsTo(
            Order::class,
        );
    }

    public function replies (): HasMany
    {
        return $this->hasMany(
            self::class,
            'parent_id',
        );
    }

    public function parent (): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'parent_id',
        );
    }
}
