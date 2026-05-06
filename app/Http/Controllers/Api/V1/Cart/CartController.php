<?php
namespace App\Http\Controllers\Api\V1\Cart;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(Request $request)
    {
        $cart = $request->attributes->get('cart')
            ->load([
                'items.product.sluggable',
                'items.variant',
                'coupon',
                'certificates',
            ]);

        return response()->json([
            'data' => new CartResource($cart),
        ]);
    }
}
