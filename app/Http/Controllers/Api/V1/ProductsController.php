<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductsBatchRequest;
use App\Http\Resources\Product\ProductCardResource;
use App\Http\Resources\Product\ProductQuickShopResource;
use App\Http\Resources\Product\ProductQuickViewResource;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductsController extends Controller
{
    public function preview(int $id, Request $request)
    {
        $product = Product::with([
                'sluggable',
                'variants',
                'categories.sluggable',
                'collections.sluggable',
            ])
            ->findOrFail($id);

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
            ->values();

        return response()->json([
            'data' => ProductCardResource::collection($products),
        ]);
    }
}
