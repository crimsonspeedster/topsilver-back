<?php
namespace App\Http\Controllers\Api\V1\Cart;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Coupon;
use App\Services\CartService;
use App\Services\CouponService;
use Illuminate\Http\Request;

class CartCouponController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected CouponService $couponService,
    ) {}

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $coupon = Coupon::where('code', $request->code)->firstOrFail();
        $cart = $request->attributes->get('cart');
        $cart = $this->couponService->applyToCart($cart, $coupon);
        $this->cartService->recalculateTotals($cart);

        return response()->json([
            'data' => new CartResource(
                $this->cartService->loadCartItems($cart),
            )
        ]);
    }

    public function destroy(Request $request)
    {
        $cart = $request->attributes->get('cart');
        $cart = $this->couponService->removeFromCart($cart);
        $this->cartService->recalculateTotals($cart);

        return response()->json([
            'data' => new CartResource(
                $this->cartService->loadCartItems($cart),
            )
        ]);
    }
}
