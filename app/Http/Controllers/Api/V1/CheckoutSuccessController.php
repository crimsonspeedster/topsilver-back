<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Bundle;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CheckoutSuccessController extends Controller
{
    public function show(string $token)
    {
        $order = Order::with([
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
            ])
            ->where('public_token', $token)
            ->firstOrFail();

        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }
}
