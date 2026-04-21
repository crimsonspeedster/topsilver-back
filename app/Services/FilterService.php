<?php

namespace App\Services;

use App\Models\FilterPage;
use App\Models\ProductFilterIndex;
use App\Models\AttributeTerm;
use App\Models\Category;
use App\Models\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FilterService
{
    public function applyFiltersToQuery($query, $taxonomy, $selected_filters = [])
    {
        $selectedFilters = $selected_filters ?: $this->parseFilters();

        if (empty($selectedFilters) && !request()->has('price')) {
            return $query;
        }

        $query->whereIn('id', function ($q) use ($taxonomy, $selectedFilters) {
            $q->from('product_filter_index')
                ->select('product_id');

            if ($taxonomy instanceof Category) {
                $q->where('category_id', $taxonomy->id);
            }

            if ($taxonomy instanceof Collection) {
                $q->where('collection_id', $taxonomy->id);
            }

            foreach ($selectedFilters as $attributeId => $termIds) {
                $q->whereExists(function ($sub) use ($attributeId, $termIds) {
                    $sub->selectRaw(1)
                        ->from('product_filter_index as pfi')
                        ->whereColumn('pfi.product_id', 'product_filter_index.product_id')
                        ->where('pfi.attribute_id', $attributeId)
                        ->whereIn('pfi.attribute_term_id', $termIds);
                });
            }

            $this->applyPriceFilter($q);
        });

        return $query;
    }

    public function getFilters($taxonomy, $filters = []): array
    {
        $selectedFilters = $filters ?: $this->parseFilters();
        $cacheKey = $this->getCacheKey($taxonomy, $selectedFilters);

        return Cache::remember($cacheKey, 60, function () use ($taxonomy, $selectedFilters) {
            return [
                'attributes' => $this->buildFilters($taxonomy, $selectedFilters),
                'price' => $this->getPriceRange($taxonomy, $selectedFilters),
            ];
        });
    }

    private function buildFilters($taxonomy, array $selectedFilters): array
    {
        $base = ProductFilterIndex::query()
            ->when($taxonomy instanceof Category, fn($q) =>
            $q->where('category_id', $taxonomy->id)
            )
            ->when($taxonomy instanceof Collection, fn($q) =>
            $q->where('collection_id', $taxonomy->id)
            );

        $baseProductIds = ProductFilterIndex::query()
            ->where('category_id', $taxonomy->id)
            ->when($taxonomy instanceof Collection, fn($q) =>
            $q->where('collection_id', $taxonomy->id)
            )
            ->where(function ($q) use ($selectedFilters) {
                foreach ($selectedFilters as $attributeId => $termIds) {
                    $q->whereExists(function ($sub) use ($attributeId, $termIds) {
                        $sub->selectRaw(1)
                            ->from('product_filter_index as pfi')
                            ->whereColumn('pfi.product_id', 'product_filter_index.product_id')
                            ->where('pfi.attribute_id', $attributeId)
                            ->whereIn('pfi.attribute_term_id', $termIds);
                    });
                }
            })
            ->select('product_id')
            ->groupBy('product_id');

        $attributeIds = (clone $base)
            ->distinct()
            ->pluck('attribute_id');

        $filters = [];

        foreach ($attributeIds as $attributeId) {

            $facets = ProductFilterIndex::query()
                ->where('attribute_id', $attributeId)
                ->whereIn('product_id', $baseProductIds)
                ->selectRaw('attribute_term_id, COUNT(DISTINCT product_id) as count')
                ->groupBy('attribute_term_id')
                ->get();

            if ($facets->isEmpty()) continue;

            $terms = AttributeTerm::with('attribute')
                ->whereIn('id', $facets->pluck('attribute_term_id'))
                ->get()
                ->keyBy('id');

            foreach ($facets as $facet) {
                $term = $terms[$facet->attribute_term_id] ?? null;
                if (!$term) continue;

                $filters[$attributeId]['attribute'] = [
                    'id' => $term->attribute->id,
                    'title' => $term->attribute->title,
                    'slug' => $term->attribute->slug,
                ];

                $filters[$attributeId]['terms'][] = [
                    'id' => $term->id,
                    'title' => $term->title,
                    'slug' => $term->slug,
                    'count' => (int) $facet->count,
                    'selected' => in_array($term->id, $selectedFilters[$attributeId] ?? []),
                ];
            }
        }

        return array_values($filters);
    }

    private function parseFilters(): array
    {
        $filters = request('filters', []);

        $result = [];

        foreach ($filters as $attributeId => $value) {
            $termIds = array_filter(
                explode(',', $value),
                fn ($v) => is_numeric($v)
            );

            if (! empty($termIds)) {
                $result[(int)$attributeId] = array_map('intval', $termIds);
            }
        }

        return $result;
    }

    public function parseFiltersFromFilterPage(FilterPage $filterPage): array
    {
        $rows = DB::table('filter_page_filters')
            ->where('filter_page_id', $filterPage->id)
            ->get();

        return $rows
            ->groupBy('attribute_id')
            ->map(function ($items) {
                return $items
                    ->pluck('attribute_term_id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->toArray();
            })
            ->toArray();
    }

    private function getCacheKey($taxonomy, array $filters): string
    {
        return 'filters_' .
            $taxonomy->getType() . '_' .
            $taxonomy->id . '_' .
            md5(json_encode($filters));
    }

    private function getPriceRange($taxonomy, array $selectedFilters): array
    {
        $productIdsQuery = ProductFilterIndex::query();

        if ($taxonomy instanceof Category) {
            $productIdsQuery->where('category_id', $taxonomy->id);
        }

        if ($taxonomy instanceof Collection) {
            $productIdsQuery->where('collection_id', $taxonomy->id);
        }

        if (!empty($selectedFilters)) {
            $productIdsQuery->groupBy('product_id');

            foreach ($selectedFilters as $attributeId => $termIds) {
                $productIdsQuery->havingRaw(
                    "SUM(attribute_id = ? AND attribute_term_id IN (" . implode(',', $termIds) . ")) > 0",
                    [$attributeId]
                );
            }
        }

        $productIds = $productIdsQuery->pluck('product_id');

        $priceQuery = ProductFilterIndex::query()
            ->whereIn('product_id', $productIds);

        return [
            'min' => (float) $priceQuery->min('price'),
            'max' => (float) $priceQuery->max('price'),
        ];
    }

    private function applyPriceFilter($q): void
    {
        $min = request()->input('price.min');
        $max = request()->input('price.max');

        if ($min === null && $max === null) {
            return;
        }

        $q->whereExists(function ($sub) use ($min, $max) {
            $sub->selectRaw(1)
                ->from('product_filter_index as p_price')
                ->whereColumn('p_price.product_id', 'product_filter_index.product_id');

            if ($min !== null) {
                $sub->where('p_price.price', '>=', $min);
            }

            if ($max !== null) {
                $sub->where('p_price.price', '<=', $max);
            }
        });
    }
}
