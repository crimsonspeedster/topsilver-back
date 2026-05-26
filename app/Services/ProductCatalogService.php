<?php

namespace App\Services;

use App\Enums\TaxonomySort;
use App\Interfaces\ContentEntityInterface;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductCatalogService
{
    public function buildCatalogQuery(
        ?ContentEntityInterface $taxonomy = null,
        ?string $search = null,
        ?int $categoryId = null,
        ?int $collectionId = null,
    ): Builder
    {
        $query = Product::query()
            ->published()
            ->with([
                'sluggable',
                'labels',
            ]);

        if ($categoryId) {
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }

        if ($collectionId) {
            $query->whereHas('collections', function ($q) use ($collectionId) {
                $q->where('collections.id', $collectionId);
            });
        }

        if ($taxonomy) {
            $query = $this->applyTaxonomy($query, $taxonomy);
        }

        if ($search) {
            $query->where(
                'title',
                'like',
                '%' . trim($search) . '%'
            );
        }

        return $query;
    }

    private function applyTaxonomy(
        Builder $query,
        ContentEntityInterface $taxonomy,
    ): Builder
    {
        return match ($taxonomy->getType()) {
            'category' => $query->whereHas(
                'categories',
                fn ($q) => $q->where('categories.id', $taxonomy->id)
            ),

            'collection' => $query->whereHas(
                'collections',
                fn ($q) => $q->where('collections.id', $taxonomy->id)
            ),

            default => $query,
        };
    }

    public function applySorting(
        Builder $query,
        TaxonomySort $sort,
    ): Builder
    {
        return match ($sort) {
            TaxonomySort::NEWEST => $query->orderBy('created_at', 'desc'),
            TaxonomySort::OLDEST => $query->orderBy('created_at', 'asc'),
            TaxonomySort::PRICE_DESC => $query->orderByRaw('COALESCE(price_on_sale, price) DESC'),
            TaxonomySort::PRICE_ASC => $query->orderByRaw('COALESCE(price_on_sale, price) ASC'),
            TaxonomySort::SELLING => $query->orderBy('selling_count', 'desc'),
        };
    }
}
