<?php
namespace App\Http\Controllers\Api\V1;

use App\Enums\EntityStatus;
use App\Enums\TaxonomySort;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\Product\ProductCardResource;
use App\Models\FilterPage;
use App\Services\FilterService;
use App\Services\TaxonomyService;

class FilterPageController extends Controller
{
    public function __construct(
        private readonly TaxonomyService $taxonomyService,
        private readonly FilterService $filterService,
    ) {}

    public function show(FilterPage $filterPage)
    {
        abort_unless($filterPage->status === EntityStatus::Published, 404);

        $category = $filterPage->category;
        $selected_filters = $this->filterService->parseFiltersFromFilterPage($filterPage);
        $products = $this->taxonomyService->getProducts($category, TaxonomySort::NEWEST, $selected_filters);
        $filters = $this->filterService->getFilters($category, $selected_filters);

        return response()->json([
            'data' => [
                'products' => ProductCardResource::collection($products->items()),
                'pagination' => new PaginationResource($products),
                'filters' => $filters,
            ],
        ]);
    }
}
