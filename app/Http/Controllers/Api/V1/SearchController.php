<?php
namespace App\Http\Controllers\Api\V1;

use App\Enums\TaxonomySort;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\Product\ProductCardResource;
use App\Services\FilterService;
use App\Services\ProductCatalogService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(
        private readonly ProductCatalogService $catalogService,
        private readonly FilterService $filterService,
    ) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['string', 'required', 'min:3'],
            'category_id' => ['nullable', 'integer'],
            'collection_id' => ['nullable', 'integer'],
        ]);

        $search = trim($validated['q']);

        $sort = TaxonomySort::tryFrom(
            $request->input('sort', 'newest')
        ) ?? TaxonomySort::NEWEST;

        $selectedFilters = $this->filterService
            ->parseFilters();

        $baseQuery = $this->catalogService
            ->buildCatalogQuery(
                search: $search,
                categoryId: $validated['category_id'] ?? null,
                collectionId: $validated['collection_id'] ?? null,
            );

        $filters = $this->filterService
            ->getFilters(
                baseQuery: $baseQuery,
                selectedFilters: $selectedFilters,
            );

        $query = $this->filterService
            ->applyFiltersToQuery(
                query: $baseQuery,
                selectedFilters: $selectedFilters,
            );

        $query = $this->catalogService
            ->applySorting(
                query: $query,
                sort: $sort,
            );

        $products = $query->paginate(12);

        return response()->json([
            'data' => [
                'products' => ProductCardResource::collection(
                    $products->items()
                ),
                'pagination' => new PaginationResource(
                    $products
                ),
                'filters' => $filters,
                'query' => $search,
            ],
        ]);
    }
}
