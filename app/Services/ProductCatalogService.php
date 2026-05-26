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
        array $categoryIds = [],
        array $collectionIds = [],
    ): Builder
    {
        $query = Product::query()
            ->published()
            ->with([
                'sluggable',
                'labels',
            ]);

        if ($taxonomy) {
            $query = $this->applyTaxonomy(
                $query,
                $taxonomy,
            );
        }

        if ($search) {
            $query->where(
                'title',
                'like',
                '%' . trim($search) . '%'
            );
        }

        if (!empty($categoryIds)) {
            $query->whereHas(
                'categories',
                fn ($q) => $q->whereIn(
                    'categories.id',
                    $categoryIds
                )
            );
        }

        if (!empty($collectionIds)) {
            $query->whereHas(
                'collections',
                fn ($q) => $q->whereIn(
                    'collections.id',
                    $collectionIds
                )
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
