<?php

namespace App\Models;

use App\Traits\HasSeo;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Collection extends Model
{
    use HasFactory, HasSeo, HasSlug;

    protected $fillable = [
        'title',
        'parent_id',
    ];

    public function products (): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_collection',
            'collection_id',
            'product_id',
        );
    }
}
