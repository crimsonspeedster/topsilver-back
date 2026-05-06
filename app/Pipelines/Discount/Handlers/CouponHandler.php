<?php
namespace App\Pipelines\Discount\Handlers;

use App\Enums\CouponTypes;
use App\Pipelines\Discount\Context\CartDiscountContext;
use App\Pipelines\Discount\Interfaces\DiscountHandler;
use Closure;

class CouponHandler implements DiscountHandler
{
    public function handle(CartDiscountContext $context, Closure $next): CartDiscountContext
    {
        $cart = $context->cart;

        if ($cart->coupon) {
            $coupon = $cart->coupon;

            $discount = match ($coupon->type) {
                CouponTypes::PERCENT => $context->subtotal * ($coupon->value / 100),
                CouponTypes::FIXED   => $coupon->value,
            };

            $discount = min($discount, $context->subtotal * 0.5);

            if ($discount > $context->subtotal) {
                $cart->coupon()->dissociate();
                $discount = 0;
            }

            $context->discount += $discount;
        }

        return $next($context);
    }
}
