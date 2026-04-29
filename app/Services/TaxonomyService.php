<?php
namespace App\Services;

use App\Enums\TaxonomySort;
use App\Interfaces\TaxonomyInterface;

class TaxonomyService
{
    public function getProducts(
        TaxonomyInterface $taxonomy,
        TaxonomySort $sort,
        array $selected_filters = [],
    )
    {
        $query = $taxonomy->products()
            ->published()
            ->with([
                'sluggable',
                'labels'
            ]);

        $query = app(FilterService::class)
            ->applyFiltersToQuery($query, $taxonomy, $selected_filters);

        $query = $this->applySorting($query, $sort);

        return $query->paginate(12);
    }

    private function applySorting($query, TaxonomySort $sort)
    {
        return match ($sort) {
            TaxonomySort::NEWEST => $query->orderBy('created_at', 'desc'),
            TaxonomySort::OLDEST => $query->orderBy('created_at', 'asc'),
            TaxonomySort::PRICE_DESC => $query->orderBy('price', 'desc'),
            TaxonomySort::PRICE_ASC => $query->orderBy('price', 'asc'),
            TaxonomySort::SELLING => $query->orderBy('selling_count', 'desc'),
        };
    }
}
