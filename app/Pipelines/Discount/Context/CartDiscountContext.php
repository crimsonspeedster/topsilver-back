<?php
namespace App\Pipelines\Discount\Context;

use App\Models\Cart;

class CartDiscountContext
{
    public function __construct(
        public Cart $cart,
        public float $subtotal,
        public float $discount = 0,
    ) {}
}
