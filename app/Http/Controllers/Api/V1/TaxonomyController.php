<?php
namespace App\Http\Controllers\Api\V1;

use App\Enums\TaxonomySort;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\Product\ProductCardResource;
use App\Http\Resources\TaxonomyCollectionResource;
use App\Models\Category;
use App\Models\Collection;
use App\Services\FilterService;
use App\Services\TaxonomyService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaxonomyController extends Controller
{
    public function __construct(
        private readonly TaxonomyService $taxonomyService,
        private readonly FilterService $filterService,
    ) {}

    public function index(string $type, Request $request)
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
                'taxonomies' => TaxonomyCollectionResource::collection($taxonomies),
                'pagination' => new PaginationResource($taxonomies),
            ]
        ]);
    }

    public function show(string $type, int $id)
    {
        $model = $this->resolveType($type);

        if (!$model) {
            abort(404);
        }

        $taxonomy = $model::query()->findOrFail($id);

        $sort = TaxonomySort::tryFrom(request('sort', 'newest'))
            ?? TaxonomySort::NEWEST;
        $products = $this->taxonomyService->getProducts($taxonomy, $sort);
        $filters = $this->filterService->getFilters($taxonomy);

        return response()->json([
            'data' => [
                'products' => ProductCardResource::collection($products->items()),
                'pagination' => new PaginationResource($products),
                'filters' => $filters,
            ],
        ]);
    }

    protected function resolveType($type)
    {
        return match ($type) {
            'category' => Category::class,
            'collection' => Collection::class,
            default => null,
        };
    }
}
