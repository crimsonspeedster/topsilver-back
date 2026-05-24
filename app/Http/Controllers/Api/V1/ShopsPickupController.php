<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShopPickupResource;
use App\Models\Shop;

class ShopsPickupController extends Controller
{
    public function __invoke()
    {
        $shops = Shop::with('city.region')->get();

        return response()->json([
            'data' => ShopPickupResource::collection($shops),
        ]);
    }
}
