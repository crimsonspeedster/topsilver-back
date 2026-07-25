<?php
namespace App\Pipelines\Discount\Calculator;

use App\Pipelines\Discount\Context\CartDiscountContext;
use App\Pipelines\Discount\Interfaces\DiscountHandler;
use Closure;

class FinalCalculator implements DiscountHandler
{
    public function handle(
        CartDiscountContext $context,
        Closure $next
    ): CartDiscountContext {
        $subtotal = $context->subtotal();

        $context->cart->subtotal = $subtotal;
        $context->cart->total = max(
            0,
            $subtotal - $context->discount
        );
        $context->cart->save();

        return $next($context);
    }
}
