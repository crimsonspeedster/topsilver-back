<?php
namespace App\Pipelines\Discount\Handlers;

use App\Pipelines\Discount\Context\CartDiscountContext;
use App\Pipelines\Discount\Interfaces\DiscountHandler;
use Closure;

class BonusHandler implements DiscountHandler
{
    public function handle(CartDiscountContext $context, Closure $next): CartDiscountContext
    {
        $max = $context->subtotal * 0.5;
        $bonus = min($context->cart->bonuses_used ?? 0, $max);

        $context->cart->bonuses_used = $bonus;
        $context->discount += $bonus;

        return $next($context);
    }
}
