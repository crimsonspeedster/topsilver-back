<?php
namespace App\Http\Controllers\Api\V1;

use App\Enums\PaymentMethods;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\CheckoutService;
use App\Services\LiqPayService;
use App\Services\MonobankPay;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService,
        protected LiqpayService $liqpayService,
        protected MonobankPay $monobankPay,
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

        switch ($order->payment_type) {
            case PaymentMethods::LIQPAY:
                $payment = [
                    'type' => PaymentMethods::LIQPAY->value,
                    'data' => $this->liqpayService->generatePaymentForm($order),
                ];
                break;
            case PaymentMethods::PLATA_BY_MONO:
                $payment = [
                    'type' => PaymentMethods::PLATA_BY_MONO->value,
                    'data' =>  $this->monobankPay->createInvoice($order),
                ];
                break;
            default:
                $payment = [
                    'type' => PaymentMethods::COD->value,
                    'data' => [],
                ];
                break;
        }

        return response()->json([
            'data' => new OrderResource(
                $order->load('items.product.sluggable')
            ),
            'payment' => $payment,
        ])->cookie(cookie()->forget('cart_token'));
    }
}
