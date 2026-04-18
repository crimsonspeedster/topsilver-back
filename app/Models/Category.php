<?php

namespace App\Models;

use App\Traits\HasSeo;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory, HasSlug, HasSeo;

    protected $fillable = [
        'title',
        'parent_id',
    ];

    public function products (): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_category',
            'category_id',
            'product_id',
        );
    }
}
