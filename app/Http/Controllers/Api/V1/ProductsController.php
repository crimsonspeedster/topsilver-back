<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductsBatchRequest;
use App\Http\Resources\Product\ProductCardResource;
use App\Http\Resources\Product\ProductQuickShopResource;
use App\Http\Resources\Product\ProductQuickViewResource;
use App\Models\ProductSubscriber;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductsController extends Controller
{
    public function preview(Product $product, Request $request)
    {
        $product->load([
            'sluggable',
            'variants',
            'labels',
            'categories.sluggable',
            'collections.sluggable',
            'promotions.sluggable',
        ]);

        $type = $request->input('type', 'quick_view');

        return response()->json([
            'data' => match ($type) {
                'quick_shop' => new ProductQuickShopResource($product),
                default => new ProductQuickViewResource($product),
            },
        ]);
    }

    public function batch(ProductsBatchRequest $request)
    {
        $ids = $request->ids;

        $products = Product::whereIn('id', $ids)
            ->get()
            ->sortBy(fn ($product) => array_search($product->id, $ids))
            ->load([
                'sluggable',
                'variants',
                'labels',
                'categories.sluggable',
                'collections.sluggable',
            ]);

        return response()->json([
            'data' => ProductCardResource::collection($products),
        ]);
    }

    public function notifications(Product $product, Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $email = $data['email'];

        ProductSubscriber::firstOrCreate(
            ['email' => $email, 'product_id' => $product->id],
        );

        return response()->json([
            'message' => 'Ви отримаєте сповіщення електронною поштою, коли цей товар знову буде в наявності.',
        ]);
    }
}
