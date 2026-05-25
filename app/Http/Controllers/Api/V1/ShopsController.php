<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\ShopSingleCollectionResource;
use App\Models\Shop;

class ShopsController extends Controller
{
    public function index()
    {
        $shops = Shop::published()
            ->with([
                'sluggable',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(6);

        return response()->json([
            'data' => [
                'shops' => ShopSingleCollectionResource::collection($shops->items()),
                'pagination' => new PaginationResource($shops),
            ]
        ]);
    }
}
