<?php
namespace App\Http\Controllers\Api\V1;

use App\Enums\PaymentMethods;
use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Bundle;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Services\CheckoutService;
use App\Services\LiqPayService;
use App\Services\MonobankPay;
use Illuminate\Database\Eloquent\Relations\MorphTo;
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

            event(new OrderCreated($order));
        } catch (ValidationException $e) {
            throw $e;
        }

        $paymentMethod = PaymentMethod::active()
            ->where('type', '=', $order->payment_type)
            ->first();

        if (!$paymentMethod) {
            abort(422, 'Payment method is not available.');
        }

        $payment = match ($paymentMethod->type) {
            PaymentMethods::LIQPAY => [
                'type' => PaymentMethods::LIQPAY->value,
                'data' => $this->getPaymentService($paymentMethod)->generatePaymentForm($order),
            ],
            PaymentMethods::PLATA_BY_MONO => [
                'type' => PaymentMethods::PLATA_BY_MONO->value,
                'data' => $this->getPaymentService($paymentMethod)->createInvoice($order),
            ],
            default => [
                'type' => PaymentMethods::COD->value,
                'data' => [],
            ],
        };

        return response()->json([
            'data' => new OrderResource(
                $order->load([
                    'certificates',
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
            ),
            'payment' => $payment,
        ])->cookie(cookie()->forget('cart_token'));
    }

    private function getPaymentService(PaymentMethod $paymentMethod): LiqPayService | MonobankPay
    {
        return match ($paymentMethod->type) {
            PaymentMethods::LIQPAY => new LiqPayService($paymentMethod),
            PaymentMethods::PLATA_BY_MONO => new MonobankPay($paymentMethod),
        };
    }
}
