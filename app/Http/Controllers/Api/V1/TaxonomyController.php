<?php
namespace App\Http\Controllers\Api\V1;

use App\Enums\TaxonomySort;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\Product\ProductCardResource;
use App\Http\Resources\TaxonomyCollectionResource;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Promotion;
use App\Services\FilterService;
use App\Services\ProductCatalogService;
use Illuminate\Http\Request;


class TaxonomyController extends Controller
{
    public function __construct(
        private readonly ProductCatalogService $catalogService,
        private readonly FilterService $filterService,
    ) {}

    public function index(
        string $type,
        Request $request,
    )

    {
        $model = $this->resolveType($type);

        if (!$model) {
            abort(404);
        }

        $taxonomies = $model::published()
            ->with([
                'sluggable',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(6);

        return response()->json([
            'data' => [
                'taxonomies' => TaxonomyCollectionResource::collection(
                    $taxonomies->items(),
                ),
                'pagination' => new PaginationResource(
                    $taxonomies
                ),
            ],
        ]);
    }

    public function show(
        string $type,
        int $id,
    )
    {
        $model = $this->resolveType($type);

        if (!$model) {
            abort(404);
        }

        $taxonomy = $model::query()
            ->findOrFail($id);

        $sort = TaxonomySort::tryFrom(
            request('sort', 'newest')
        ) ?? TaxonomySort::NEWEST;

        $baseQuery = $this->catalogService
            ->buildCatalogQuery(
                taxonomy: $taxonomy,
            );

        $filters = $this->filterService
            ->getFilters($baseQuery);

        $query = $this->filterService
            ->applyFiltersToQuery(
                query: $baseQuery,
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
            ],
        ]);
    }

    protected function resolveType(
        string $type
    ): ?string
    {
        return match ($type) {
            'category' => Category::class,
            'collection' => Collection::class,
            'promotion' => Promotion::class,
            default => null,
        };
    }
}
