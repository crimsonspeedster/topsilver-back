<?php
namespace App\Pipelines\Discount\Handlers;

use App\Pipelines\Discount\Context\CartDiscountContext;
use App\Pipelines\Discount\Interfaces\DiscountHandler;
use App\Services\CartService;
use Closure;

class PromotionCouponCompatibilityHandler implements DiscountHandler
{
    public function __construct(
        protected CartService $cartService,
    ) {}

    public function handle(
        CartDiscountContext $context,
        Closure $next
    ): CartDiscountContext
    {
        $cart = $context->cart;

        if (
            $cart->coupon_id &&
            $this->cartService->hasPromotionProducts($cart)
        ) {
            $cart->coupon()->dissociate();
            $cart->save();
        }

        return $next($context);
    }
}
