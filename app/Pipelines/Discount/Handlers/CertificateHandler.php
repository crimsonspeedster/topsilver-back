<?php
namespace App\Pipelines\Discount\Handlers;

use App\Pipelines\Discount\Context\CartDiscountContext;
use App\Pipelines\Discount\Interfaces\DiscountHandler;
use Closure;

class CertificateHandler implements DiscountHandler
{
    public function handle(CartDiscountContext $context, Closure $next): CartDiscountContext
    {
        $cart = $context->cart;
        $valid = [];

        foreach ($cart->certificates as $certificate) {
            if (($context->subtotal - $context->discount) < $certificate->value) {
                continue;
            }

            $context->discount += $certificate->value;
            $valid[] = $certificate->id;
        }

        $cart->certificates()->sync($valid);
        return $next($context);
    }
}
