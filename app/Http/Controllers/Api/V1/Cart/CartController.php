<?php
namespace App\Http\Controllers\Api\V1\Cart;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Bundle;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(Request $request)
    {
        $cart = $request->attributes->get('cart')
            ->load([
                'items.entity' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Product::class => [
                            'sluggable',
                        ],

                        Bundle::class => [
                            'items.product.sluggable',
                        ],
                    ]);
                },
                'items.variant',
                'coupon',
                'certificates',
            ]);

        return response()->json([
            'data' => new CartResource($cart),
        ]);
    }
}
