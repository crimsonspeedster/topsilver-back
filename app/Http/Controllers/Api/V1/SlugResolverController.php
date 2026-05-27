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
use App\Http\Resources\SeoPageResource;
use App\Http\Resources\ShopSingleResource;
use App\Http\Resources\TaxonomyCollectionResource;
use App\Http\Resources\TaxonomyResource;
use App\Models\Category;
use App\Models\Collection;
use App\Models\ContentEntity;
use App\Models\FilterPage;
use App\Models\Page;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Shop;
use App\Models\Slug;
use App\Models\TaxonomyEntity;
use App\Services\FilterService;
use App\Services\ProductCatalogService;
use App\Services\ProductService;


class SlugResolverController extends Controller
{
    public function __construct(
        private readonly ProductCatalogService $catalogService,
        private readonly FilterService         $filterService,
        private readonly ProductService        $productService,
    ) {}

    public function resolver(string $slug)
    {
        $slugModel = Slug::where('slug', $slug)->firstOrFail();
        $entity = $slugModel->entity;

        abort_unless($entity->status === EntityStatus::Published, 404);

        return match (true) {
            $entity instanceof Product => $this->resolverProduct($entity),

            $entity instanceof Shop => $this->resolverShopEntity($entity),

            $entity instanceof Page => $this->resolverContentEntity($entity),

            $entity instanceof Promotion,
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
        ]);

        return response()->json([
            'data' => new SeoPageResource($entity),
        ]);
    }

    private function resolverShopEntity(Shop $entity)
    {
        $entity->load([
            'seoBlock',
        ]);

        return response()->json([
            'data' => [
                'type' => $entity->getType(),
                'entity' => new ShopSingleResource($entity),
            ],
        ]);
    }

    private function resolverContentEntity(ContentEntity $entity)
    {
        $entity->load([
            'seoBlock',
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
            'promotions.sluggable',
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

    private function resolverFilterPage(
        FilterPage $filterPage,
    )
    {
        $category = $filterPage->category->load(['sluggable']);

        $selectedFilters = $this->filterService
            ->resolveSelectedFiltersFromFilterPage($filterPage);

        $filterPage->load([
            'seoBlock',
        ]);

        return $this->resolveTaxonomyBase(
            taxonomy: $category,
            selectedFilters: $selectedFilters,
            sort: TaxonomySort::NEWEST,
            extra: [
                'entity' => new FilterPageResource($filterPage),
                'category' => new TaxonomyCollectionResource($category),
                'type' => 'filter_page',
            ]
        );
    }

    private function resolveTaxonomyBase(
        TaxonomyEntity $taxonomy,
        array          $selectedFilters = [],
        TaxonomySort   $sort = TaxonomySort::NEWEST,
        array          $extra = [],
    )
    {
        $baseQuery = $this->catalogService
            ->buildCatalogQuery(
                taxonomy: $taxonomy,
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

        $taxonomy->load([
            'seoBlock',
        ]);

        return response()->json([
            'data' => array_merge([
                'type' => $taxonomy->getType(),
                'entity' => new TaxonomyResource(
                    $taxonomy
                ),
                'products' => ProductCardResource::collection(
                    $products->items()
                ),
                'pagination' => new PaginationResource(
                    $products
                ),
                'filters' => $filters,
            ], $extra),
        ]);
    }
}
