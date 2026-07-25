<?php
namespace App\Pipelines\Discount\Context;

use App\Models\Cart;

class CartDiscountContext
{
    public function __construct(
        public Cart $cart,
        public float $discount = 0,
    ) {}

    public function subtotal(): float
    {
        return $this->cart->items->sum(function ($item) {
            return ($item->price * $item->quantity)
                - $item->promotion_discount;
        });
    }
}
