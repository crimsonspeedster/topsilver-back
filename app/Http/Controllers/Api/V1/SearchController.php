<?php
namespace App\Http\Controllers\Api\V1;

use App\Enums\TaxonomySort;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\Product\ProductCardResource;
use App\Models\Category;
use App\Models\Collection;
use App\Services\FilterService;
use App\Services\ProductCatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    public function __construct(
        private readonly ProductCatalogService $catalogService,
        private readonly FilterService $filterService,
    ) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['string', 'required', 'min:3'],
        ]);

        $search = trim($validated['search']);

        $sort = TaxonomySort::tryFrom(
            $request->input('sort', 'newest')
        ) ?? TaxonomySort::NEWEST;

        $selectedFilters = $this->filterService->parseFilters();
        $selectedCollections = $this->filterService->parseTaxonomies('collections');
        $selectedCategories = $this->filterService->parseTaxonomies('categories');

        $baseQuery = $this->catalogService
            ->buildCatalogQuery(
                search: $search,
                categoryIds: $selectedCategories,
                collectionIds: $selectedCollections,
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

        $categories = Cache::remember(
            'search_categories',
            now()->addDay(),
            fn () => Category::query()
                ->select('id', 'title')
                ->get()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                ])
                ->toArray()
        );

        $categories = array_map(
            fn ($item) => [
                ...$item,
                'selected' => in_array($item['id'], $selectedCategories),
            ],
            $categories
        );

        $collections = Cache::remember(
            'search_collections',
            now()->addDay(),
            fn () => Collection::query()
                ->select('id', 'title')
                ->get()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                ])
                ->toArray()
        );

        $collections = array_map(
            fn ($item) => [
                ...$item,
                'selected' => in_array($item['id'], $selectedCollections),
            ],
            $collections
        );

        return response()->json([
            'data' => [
                'products' => ProductCardResource::collection(
                    $products->items()
                ),
                'pagination' => new PaginationResource(
                    $products
                ),
                'categories' => $categories,
                'collections' => $collections,
                'filters' => $filters,
                'query' => $search,
            ],
        ]);
    }
}
