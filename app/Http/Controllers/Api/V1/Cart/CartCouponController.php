<?php
namespace App\Http\Controllers\Api\V1\Cart;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Coupon;
use App\Services\CartService;
use Exception;
use Illuminate\Http\Request;

class CartCouponController extends Controller
{
    public function __construct(
        protected CartService $cartService,
    ) {}

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $coupon = Coupon::where('code', $request->code)->firstOrFail();
        $cart = $request->attributes->get('cart') ?? $this->cartService->getOrCreateCart($request);

        try {
            $cart = $this->cartService->addCoupon($cart, $coupon);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'data' => new CartResource(
                $this->cartService->loadCartItems($cart),
            )
        ]);
    }

    public function destroy(Request $request)
    {
        $cart = $request->attributes->get('cart') ?? $this->cartService->getOrCreateCart($request);
        $cart = $this->cartService->removeCoupon($cart);

        return response()->json([
            'data' => new CartResource(
                $this->cartService->loadCartItems($cart),
            )
        ]);
    }
}
