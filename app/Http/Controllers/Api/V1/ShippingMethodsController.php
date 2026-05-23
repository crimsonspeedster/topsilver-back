<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShippingMethodResource;
use App\Models\ShippingMethod;

class ShippingMethodsController extends Controller
{
    public function __invoke()
    {
        $methods = ShippingMethod::active()
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => ShippingMethodResource::collection($methods),
        ]);
    }
}
