<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EntityStatus;
use App\Enums\ReviewStatus;
use App\Enums\TaxonomySort;
use App\Http\Controllers\Controller;
use App\Http\Resources\ContentEntityResource;
use App\Http\Resources\FilterPageResource;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\Product\ProductCardResource;
use App\Http\Resources\Product\ProductPDPResource;
use App\Http\Resources\ProductReviewResource;
use App\Http\Resources\TaxonomyResource;
use App\Models\Category;
use App\Models\Collection;
use App\Models\ContentEntity;
use App\Models\FilterPage;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\Slug;
use App\Models\TaxonomyEntity;
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

            $entity instanceof Post,
            $entity instanceof Page => $this->resolverContentEntity($entity),

            $entity instanceof Category,
            $entity instanceof Collection => $this->resolverTaxonomy($entity),

            $entity instanceof FilterPage => $this->resolverFilterPage($entity),
        };
    }

    private function resolverContentEntity (ContentEntity $entity)
    {
        abort_unless($entity->status === EntityStatus::Published, 404);

        $entity->load([
            'seo',
            'seoBlock',
            'media'
        ]);

        return response()->json([
            'data' => [
                'type' => $entity->getType(),
                'entity' => new ContentEntityResource($entity),
            ],
        ]);
    }

    private function resolverProduct(Product $product)
    {
        abort_unless($product->status === EntityStatus::Published, 404);

        $reviews = $product->reviews()
            ->where('status', ReviewStatus::APPROVED)
            ->whereNull('parent_id')
            ->with([
                'user.profile',
            ])
            ->withCount([
                'replies as replies_count' => function ($q) {
                    $q->where('status', ReviewStatus::APPROVED);
                }
            ])
            ->limit(5)
            ->get();

        $reviewsCount = $product->reviews()
            ->where('status', ReviewStatus::APPROVED)
            ->whereNull('parent_id')
            ->count();

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
            'seoBlock',
        ]);

        return response()->json([
            'data' => [
                'type' => 'product',
                'entity' => new ProductPDPResource($product),
                'reviews' => ProductReviewResource::collection($reviews),
                'reviews_pagination' => [
                    'total' => $reviewsCount,
                    'per_page' => 5,
                ],
            ],
        ]);
    }

    private function resolverTaxonomy (TaxonomyEntity $taxonomy)
    {
        $sort = TaxonomySort::tryFrom(request('sort', 'newest'))
                ?? TaxonomySort::NEWEST;
        $products = $this->taxonomyService->getProducts($taxonomy, $sort);
        $filters = $this->filterService->getFilters($taxonomy);

        $taxonomy->load([
            'seo',
            'seoBlock',
        ]);

        return response()->json([
            'data' => [
                'type' => $taxonomy->getType(),
                'entity' => new TaxonomyResource($taxonomy),
                'products' => ProductCardResource::collection($products->items()),
                'pagination' => new PaginationResource($products),
                'filters' => $filters,
            ],
        ]);
    }

    private function resolverFilterPage (FilterPage $filterPage)
    {
        abort_unless($filterPage->status === EntityStatus::Published, 404);

        $category = $filterPage->category;
        $selected_filters = $this->filterService->parseFiltersFromFilterPage($filterPage);
        $products = $this->taxonomyService->getProducts($category, TaxonomySort::NEWEST, $selected_filters);
        $filters = $this->filterService->getFilters($category, $selected_filters);

        $filterPage->load([
            'seo',
            'seoBlock',
        ]);

        return response()->json([
            'data' => [
                'type' => 'filter_page',
                'entity' => new FilterPageResource($filterPage),
                'products' => ProductCardResource::collection($products->items()),
                'pagination' => new PaginationResource($products),
                'filters' => $filters,
            ],
        ]);
    }
}
