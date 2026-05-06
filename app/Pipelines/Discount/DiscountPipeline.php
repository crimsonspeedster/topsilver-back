<?php
namespace App\Pipelines\Discount;

use App\Pipelines\Discount\Context\CartDiscountContext;
use App\Pipelines\Discount\Interfaces\DiscountHandler;

class DiscountPipeline
{
    /** @var DiscountHandler[] */
    protected array $handlers = [];

    public function send(CartDiscountContext $context): CartDiscountContext
    {
        $pipeline = array_reduce(
            array_reverse($this->handlers),
            fn ($next, $handler) =>
            fn ($ctx) => $handler->handle($ctx, $next),
            fn ($ctx) => $ctx
        );

        return $pipeline($context);
    }

    public function through(array $handlers): self
    {
        $this->handlers = $handlers;
        return $this;
    }
}
