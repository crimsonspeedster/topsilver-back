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
        array $promotionIds = [],
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

        if (!empty($promotionIds)) {
            $query->whereHas(
                'promotions',
                fn ($q) => $q->whereIn(
                    'promotions.id',
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
                'filterIndex',
                fn ($q) => $q->where(
                    'category_id',
                    $taxonomy->id
                )
            ),

            'collection' => $query->whereHas(
                'filterIndex',
                fn ($q) => $q->where(
                    'collection_id',
                    $taxonomy->id
                )
            ),

            'promotion' => $query->whereHas(
                'promotions',
                fn ($q) => $q->where('promotions.id', $taxonomy->id)
            ),

            default => $query,
        };
    }

    private function applyStockPriority(Builder $query): Builder
    {
        return $query->orderByRaw("
            CASE
                WHEN stock_status = 'in_stock' THEN 0
                WHEN stock_status = 'out_of_stock' THEN 1
            END
        ");
    }

    public function applySorting(
        Builder $query,
        TaxonomySort $sort,
    ): Builder
    {
        $query = $this->applyStockPriority($query);

        return match ($sort) {
            TaxonomySort::NEWEST => $query->orderBy('created_at', 'desc'),
            TaxonomySort::OLDEST => $query->orderBy('created_at', 'asc'),
            TaxonomySort::PRICE_DESC => $query->orderByRaw('COALESCE(price_on_sale, price) DESC'),
            TaxonomySort::PRICE_ASC => $query->orderByRaw('COALESCE(price_on_sale, price) ASC'),
            TaxonomySort::SELLING => $query->orderBy('selling_count', 'desc'),
        };
    }
}
