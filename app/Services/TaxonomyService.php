<?php
namespace App\Services;

use App\Enums\TaxonomySort;
use App\Interfaces\TaxonomyInterface;

class TaxonomyService
{
    public function getProducts(
        TaxonomyInterface $taxonomy,
        TaxonomySort $sort,
    )
    {
        $query = $taxonomy->products()
            ->published()
            ->with([
                'sluggable',
                'labels'
            ]);

        $query = app(FilterService::class)
            ->applyFiltersToQuery($query, $taxonomy);

        $query = $this->applySorting($query, $sort);

        return $query->paginate(12);
    }

    private function applySorting($query, TaxonomySort $sort)
    {
        return match ($sort) {
            TaxonomySort::NEWEST => $query->orderBy('created_at', 'desc'),
            TaxonomySort::OLDEST => $query->orderBy('created_at', 'asc'),
            TaxonomySort::ALPHA_ASC => $query->orderBy('title', 'asc'),
            TaxonomySort::ALPHA_DESC => $query->orderBy('title', 'desc'),
        };
    }
}
