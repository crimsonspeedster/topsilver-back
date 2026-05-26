<?php
namespace App\Services;

use App\Interfaces\ContentEntityInterface;
use App\Models\AttributeTerm;
use App\Models\Category;
use App\Models\Collection;
use App\Models\FilterPage;
use App\Models\ProductFilterIndex;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FilterService
{
    public function applyFiltersToQuery(
        Builder $query,
        array   $selectedFilters = [],
        ?ContentEntityInterface $taxonomy = null,
    ): Builder
    {
        $selectedFilters = $selectedFilters ?: $this->parseFilters();

        if (
            empty($selectedFilters)
            && !request()->has('price')
        ) {
            return $query;
        }

        $productIdsQuery = ProductFilterIndex::query();

        if ($taxonomy instanceof Category) {
            $productIdsQuery->where('category_id', $taxonomy->id);
        }

        if ($taxonomy instanceof Collection) {
            $productIdsQuery->where('collection_id', $taxonomy->id);
        }

        $productIdsQuery = $productIdsQuery
            ->select('product_id');

        foreach ($selectedFilters as $attributeId => $termIds) {
            $productIdsQuery->whereExists(function ($sub) use ($attributeId, $termIds) {
                $sub->selectRaw(1)
                    ->from('product_filter_index as pfi')
                    ->whereColumn('pfi.product_id', 'product_filter_index.product_id')
                    ->where('pfi.attribute_id', $attributeId)
                    ->whereIn('pfi.attribute_term_id', $termIds);
            });
        }

        $this->applyPriceFilter($productIdsQuery);

        return $query->whereIn(
            'id',
            $productIdsQuery->pluck('product_id')
        );
    }

    public function getFilters(
        Builder $baseQuery,
        array   $selectedFilters = [],
    ): array
    {
        $selectedFilters = $selectedFilters ?: $this->parseFilters();

        $productIds = (clone $baseQuery)
            ->pluck('products.id');

        $cacheKey = $this->getCacheKey(
            $productIds->toArray(),
            $selectedFilters,
        );

        return Cache::remember(
            $cacheKey,
            60,
            function () use (
                $productIds,
                $selectedFilters
            ) {
                return [
                    'attributes' => $this->buildFilters(
                        $productIds->toArray(),
                        $selectedFilters,
                    ),

                    'price' => $this->getPriceRange(
                        $productIds->toArray(),
                        $selectedFilters,
                    ),
                ];
            }
        );
    }

    private function buildFilters(
        array $productIds,
        array $selectedFilters,
    ): array
    {
        $base = ProductFilterIndex::query()
            ->whereIn('product_id', $productIds);

        $attributeIds = (clone $base)
            ->distinct()
            ->pluck('attribute_id');

        $filters = [];

        foreach ($attributeIds as $attributeId) {

            $facets = ProductFilterIndex::query()
                ->where('attribute_id', $attributeId)
                ->whereIn('product_id', $productIds)
                ->selectRaw(
                    'attribute_term_id,
                     COUNT(DISTINCT product_id) as count'
                )
                ->groupBy('attribute_term_id')
                ->get();

            if ($facets->isEmpty()) {
                continue;
            }

            $terms = AttributeTerm::with('attribute')
                ->whereIn(
                    'id',
                    $facets->pluck('attribute_term_id')
                )
                ->get()
                ->keyBy('id');

            foreach ($facets as $facet) {

                $term = $terms[$facet->attribute_term_id] ?? null;

                if (!$term) {
                    continue;
                }

                $filters[$attributeId]['attribute'] = [
                    'id' => $term->attribute->id,
                    'title' => $term->attribute->title,
                    'slug' => $term->attribute->slug,
                    'type' => $term->attribute->type,
                ];

                $filters[$attributeId]['terms'][] = [
                    'id' => $term->id,
                    'title' => $term->title,
                    'slug' => $term->slug,
                    'meta_value' => $term->meta_value,
                    'count' => (int)$facet->count,
                    'selected' => in_array(
                        $term->id,
                        $selectedFilters[$attributeId] ?? []
                    ),
                ];
            }
        }

        return array_values($filters);
    }

    private function getPriceRange(
        array $productIds,
        array $selectedFilters,
    ): array
    {
        $query = ProductFilterIndex::query()
            ->whereIn('product_id', $productIds);

        $productIdsQuery = ProductFilterIndex::query()
            ->select('product_id');

        foreach ($selectedFilters as $attributeId => $termIds) {
            $productIdsQuery->whereExists(function ($sub) use ($attributeId, $termIds) {
                $sub->selectRaw(1)
                    ->from('product_filter_index as pfi')
                    ->whereColumn('pfi.product_id', 'product_filter_index.product_id')
                    ->where('pfi.attribute_id', $attributeId)
                    ->whereIn('pfi.attribute_term_id', $termIds);
            });
        }

        return [
            'min' => (float)$query->min('price'),
            'max' => (float)$query->max('price'),
        ];
    }

    private function applyPriceFilter($query): void
    {
        $min = request()->input('price.min');
        $max = request()->input('price.max');

        if ($min === null && $max === null) {
            return;
        }

        if ($min !== null) {
            $query->where('price', '>=', $min);
        }

        if ($max !== null) {
            $query->where('price', '<=', $max);
        }
    }

    public function parseFilters(): array
    {
        $filters = request('filters', []);
        $result = [];

        foreach ($filters as $id => $value) {
            $ids = array_filter(
                explode(',', $value),
                fn($v) => is_numeric($v)
            );

            if (!empty($ids)) {
                $result[(int)$id] = array_map(
                    'intval',
                    $ids
                );
            }
        }

        return $result;
    }

    public function parseTaxonomies(
        string $key,
    ): array
    {
        return collect(request($key, []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray();
    }

    public function parseFiltersFromFilterPage(FilterPage $filterPage): array
    {
        return Cache::remember(
            "filter_page_filters_{$filterPage->id}",
            60,
            function () use ($filterPage) {

                $rows = DB::table('filter_page_filters')
                    ->where('filter_page_id', $filterPage->id)
                    ->get();

                return $rows
                    ->groupBy('attribute_id')
                    ->map(fn ($items) => $items
                        ->pluck('attribute_term_id')
                        ->map(fn ($id) => (int) $id)
                        ->values()
                        ->toArray()
                    )
                    ->toArray();
            }
        );
    }

    public function resolveSelectedFiltersFromFilterPage(
        FilterPage $filterPage,
    ): array
    {
        $requestFilters = $this->parseFilters();

        if (!empty($requestFilters)) {
            return $requestFilters;
        }

        return $this->parseFiltersFromFilterPage($filterPage);
    }

    private function getCacheKey(
        array $productIds,
        array $filters,
    ): string
    {
        return 'filters_' .
            md5(json_encode($productIds)) .
            '_' .
            md5(json_encode($filters));
    }
}
