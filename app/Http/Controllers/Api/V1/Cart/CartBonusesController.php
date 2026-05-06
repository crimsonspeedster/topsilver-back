<?php
namespace App\Http\Controllers\Api\V1\Cart;

use App\Http\Controllers\Controller;
use App\Http\Resources\BonusResource;
use App\Http\Resources\CartResource;
use App\Services\BonusService;
use App\Services\CartService;
use Illuminate\Http\Request;
use Exception;

class CartBonusesController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected BonusService $bonusService
    ) {}

    public function apply(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $cart = $request->attributes->get('cart');
        $user = $request->user();

        try {
            $cart = $this->cartService->setBonuses($cart, $user, $data['amount']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'data' => [
                'cart' => new CartResource(
                    $this->cartService->loadCartItems($cart),
                ),
            ]
        ]);
    }
}
