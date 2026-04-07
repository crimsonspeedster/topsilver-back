<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EntityStatus;
use App\Enums\ProductTypes;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductCollectionResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\Slug;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SlugResolverController extends Controller
{
    public function resolver (string $slug)
    {
        $slugModel = Slug::where('slug', $slug)->firstOrFail();
        $entity = $slugModel->entity;

        switch (get_class($entity)) {
            case Product::class:
                return $this->resolverProduct(
                    $entity->load([
                        'categories',
                        'seo',
                    ])
                );

            case Category::class:
                $category = $entity;
                $products = $category->products()
                    ->paginate(12);

                return $this->resolverCategory($category->load('seo'), $products);

            default:
                abort(404);
        }
    }

    private function resolverProduct (Product $product)
    {
        abort_unless($product->status === EntityStatus::Published, 404);
        abort_if($product->type === ProductTypes::Variable, 404);

        return (new ProductResource($product))
            ->additional([
                'type' => 'product',
            ]);
    }

    private function resolverCategory (Category $category, LengthAwarePaginator $products)
    {
        return (new CategoryResource($category))
            ->additional([
                'type' => 'category',
                'products' => ProductCollectionResource::collection($products),
                'pagination' => [
                    'total_pages' => $products->lastPage(),
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total_items' => $products->total(),
                ],
            ]);
    }
}
