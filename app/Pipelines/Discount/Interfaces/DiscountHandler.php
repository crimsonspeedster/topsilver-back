<?php
namespace App\Pipelines\Discount\Interfaces;

use App\Pipelines\Discount\Context\CartDiscountContext;
use Closure;

interface DiscountHandler
{
    public function handle(CartDiscountContext $context, Closure $next): CartDiscountContext;
}
