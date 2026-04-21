<?php

namespace App\Models;

use App\Interfaces\TaxonomyInterface;
use App\Traits\HasSeo;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model implements TaxonomyInterface
{
    use HasFactory, HasSlug, HasSeo;

    protected $fillable = [
        'title',
        'parent_id',
    ];

    public function getType(): string
    {
        return 'category';
    }

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
