<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductsBatchRequest;
use App\Http\Resources\ProductCollectionResource;
use App\Models\Product;

class ProductsController extends Controller
{
    public function index() {


        return response()->json([
            'data' => 'test',
        ]);
    }

    public function show(int $id)
    {
        $product = Product::findOrFail($id);

        return response()->json([
            'data' => new ProductCollectionResource($product),
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
            'data' => ProductCollectionResource::collection($products),
        ]);
    }
}
