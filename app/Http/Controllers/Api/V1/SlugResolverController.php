<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EntityStatus;
use App\Enums\ReviewStatus;
use App\Enums\TaxonomySort;
use App\Http\Controllers\Controller;
use App\Http\Resources\ContentEntityResource;
use App\Http\Resources\FilterPageResource;
use App\Http\Resources\MediaResource;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\Product\ProductCardResource;
use App\Http\Resources\Product\ProductPDPResource;
use App\Http\Resources\ProductReviewResource;
use App\Http\Resources\SeoPageResource;
use App\Http\Resources\SeoResource;
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
use App\Services\ProductService;
use App\Services\TaxonomyService;

class SlugResolverController extends Controller
{
    public function __construct(
        private readonly TaxonomyService $taxonomyService,
        private readonly FilterService $filterService,
        private readonly ProductService $productService,
    ) {}

    public function resolver (string $slug)
    {
        $slugModel = Slug::where('slug', $slug)->firstOrFail();
        $entity = $slugModel->entity;

        abort_unless($entity->status === EntityStatus::Published, 404);

        return match (true) {
            $entity instanceof Product => $this->resolverProduct($entity),

            $entity instanceof Post,
            $entity instanceof Page => $this->resolverContentEntity($entity),

            $entity instanceof Category,
            $entity instanceof Collection => $this->resolverTaxonomy($entity),

            $entity instanceof FilterPage => $this->resolverFilterPage($entity),
        };
    }

    public function seo(string $slug)
    {
        $slugModel = Slug::where('slug', $slug)->firstOrFail();
        $entity = $slugModel->entity;

        abort_unless($entity->status === EntityStatus::Published, 404);

        $entity->load([
            'seo',
            'media',
        ]);

        return response()->json([
            'data' => new SeoPageResource($entity),
        ]);
    }

    private function resolverContentEntity (ContentEntity $entity)
    {
        $entity->load([
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
            'crossSellsLimited.labels',
            'groupProducts.sluggable',
            'groupProducts.labels',
            'seoBlock',
            'videos',
        ]);

        $breadcrumbs = $this->productService->getBreadcrumbs($product);
        $prev = $this->productService->getPrev($product);
        $next = $this->productService->getNext($product);

        return response()->json([
            'data' => [
                'type' => 'product',
                'entity' => new ProductPDPResource($product),
                'breadcrumbs' => $breadcrumbs,
                'prev_next' => [
                    'prev' => $prev ? new ProductCardResource($prev) : null,
                    'next' => $next ? new ProductCardResource($next) : null,
                ],
                'reviews' => ProductReviewResource::collection($reviews),
                'reviews_pagination' => [
                    'total' => $reviewsCount,
                    'per_page' => 5,
                ],
            ],
        ]);
    }

    private function resolverTaxonomy(TaxonomyEntity $taxonomy)
    {
        $sort = TaxonomySort::tryFrom(request('sort', 'newest'))
            ?? TaxonomySort::NEWEST;

        return $this->resolveTaxonomyBase(
            taxonomy: $taxonomy,
            sort: $sort
        );
    }

    private function resolverFilterPage(FilterPage $filterPage)
    {
        $category = $filterPage->category;

        $selectedFilters = $this->filterService
            ->parseFiltersFromFilterPage($filterPage);

        $filterPage->load([
            'seoBlock',
        ]);

        return $this->resolveTaxonomyBase(
            taxonomy: $category,
            selectedFilters: $selectedFilters,
            sort: TaxonomySort::NEWEST,
            extra: [
                'entity' => new FilterPageResource($filterPage),
                'type' => 'filter_page',
            ]
        );
    }

    private function resolveTaxonomyBase(
        TaxonomyEntity $taxonomy,
        array $selectedFilters = [],
        TaxonomySort $sort = TaxonomySort::NEWEST,
        array $extra = []
    ) {
        $products = $this->taxonomyService->getProducts(
            $taxonomy,
            $sort,
            $selectedFilters
        );

        $filters = $this->filterService->getFilters(
            $taxonomy,
            $selectedFilters
        );

        $taxonomy->load([
            'seoBlock',
        ]);

        return response()->json([
            'data' => array_merge([
                'type' => $taxonomy->getType(),
                'entity' => new TaxonomyResource($taxonomy),
                'products' => ProductCardResource::collection($products->items()),
                'pagination' => new PaginationResource($products),
                'filters' => $filters,
            ], $extra),
        ]);
    }
}
