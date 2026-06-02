<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wishlist extends Model
{
    protected $fillable = [
        'user_id',
        'wishlist_token',
        'last_modified'
    ];

    protected $casts = [
        'last_modified' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(
            WishlistItem::class,
        );
    }

    public function getItemsCountAttribute(): int
    {
        return $this->items()->count();
    }
}
