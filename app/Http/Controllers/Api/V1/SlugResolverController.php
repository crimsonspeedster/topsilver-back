<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EntityStatus;
use App\Enums\TaxonomySort;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\Product\ProductCardResource;
use App\Http\Resources\Product\ProductPDPResource;
use App\Http\Resources\TaxonomyResource;
use App\Interfaces\TaxonomyInterface;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\Slug;
use App\Services\FilterService;
use App\Services\TaxonomyService;

class SlugResolverController extends Controller
{
    public function __construct(
        private readonly TaxonomyService $taxonomyService,
        private readonly FilterService $filterService,
    ) {}

    public function resolver (string $slug)
    {
        $slugModel = Slug::where('slug', $slug)->firstOrFail();
        $entity = $slugModel->entity;

        return match (true) {
            $entity instanceof Product => $this->resolverProduct($entity),

            $entity instanceof Category,
            $entity instanceof Collection => $this->resolverTaxonomy($entity),
        };
    }

    private function resolverProduct(Product $product)
    {
        abort_unless($product->status === EntityStatus::Published, 404);

        $product->load([
            'categories.sluggable',
            'collections.sluggable',
            'labels',
            'bundles.items.product.sluggable',
            'variants',
            'attributeTerms.attribute',
            'crossSellsLimited.sluggable',
            'groupProducts.sluggable',
            'seo',
        ]);

        return response()->json([
            'data' => [
                'type' => 'product',
                'product' => new ProductPDPResource($product),
            ],
        ]);
    }

    private function resolverTaxonomy (TaxonomyInterface $taxonomy)
    {
        $sort = TaxonomySort::tryFrom(request('sort', 'newest'))
                ?? TaxonomySort::NEWEST;
        $products = $this->taxonomyService->getProducts($taxonomy, $sort);
        $filters = $this->filterService->getFilters($taxonomy);

        $taxonomy->load('seo');

        return response()->json([
            'data' => [
                'type' => $taxonomy->getType(),
                'category' => new TaxonomyResource($taxonomy),
                'products' => ProductCardResource::collection($products->items()),
                'pagination' => new PaginationResource($products),
                'filters' => $filters,
            ],
        ]);
    }
}
