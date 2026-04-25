<?php
namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethods;
use App\Enums\ShippingMethods;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService,
    ) {}

    public function __invoke(CreateOrderRequest $request)
    {
        $cart = $request->attributes->get('cart');

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'message' => 'Cart is empty',
            ], 422);
        }

        try {
            $order = $this->checkoutService->checkout(
                $cart,
                $request->validated()
            );
        } catch (ValidationException $e) {
            throw $e;
        }

        return response()->json([
            'data' => new OrderResource(
                $order->load('items.product.sluggable')
            ),
        ])->cookie(cookie()->forget('cart_token'));
    }
}
