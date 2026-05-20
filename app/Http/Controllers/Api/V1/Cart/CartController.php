<?php
namespace App\Http\Controllers\Api\V1\Cart;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Bundle;
use App\Models\Product;
use App\Services\CartService;
use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected CurrencyService $currencyService,
        protected CartService $cartService,
    ) {}

    public function show(Request $request)
    {
        $cart = $request->attributes->get('cart');

        if (!$cart) {
            $total = 0;
            $subtotal = 0;

            return response()->json([
                'data' => [
                    'items' => [],
                    'subtotal' => '0',
                    'total' => '0',
                    'bonuses_used' => 0,
                    'total_formatted' => $this->currencyService->format($total)->format(),
                    'subtotal_formatted' => $this->currencyService->format($subtotal)->format(),
                    'coupon' => null,
                    'certificates' => [],
                    'items_count' => 0,
                    'total_qty' => 0,
                ]
            ]);
        }

        return response()->json([
            'data' => new CartResource(
                $this->cartService->loadCartItems($cart),
            ),
        ]);
    }
}
