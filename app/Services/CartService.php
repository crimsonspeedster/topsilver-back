<?php
namespace App\Services;

use App\Enums\StockStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class CartService
{
    public function resolveCartItem(Cart $cart, Product $product, ProductVariant|null $variant = null): Builder
    {
        return CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->when(
                $variant,
                fn ($q) => $q->where('product_variant_id', $variant->id),
                fn ($q) => $q->whereNull('product_variant_id')
            );
    }

    public function getAvailableStock(Product $product, ?ProductVariant $variant = null): int
    {
        $max = $product->stock_status === StockStatus::OutOfStock ? 0 : 999;
        $stock = $product->manage_stock
            ? min($product->stock, $max)
            : $max;

        if ($variant) {
            $stock = min($variant->stock, $max);
        }

        return $stock;
    }

    public function loadCartItems(Cart $cart): Cart
    {
        return $cart->fresh([
            'items.product.sluggable',
            'items.variant',
            'coupon',
        ]);
    }

    public function recalculateTotals(Cart $cart): Cart
    {
        $cart = $cart->fresh(['coupon']);

        $subtotal = CartItem::where('cart_id', $cart->id)
            ->selectRaw('SUM(price * quantity) as total')
            ->value('total') ?? 0;

        $discount = 0;

        if ($cart->coupon) {
            $discount = app(CouponService::class)
                ->calculateDiscount($cart, $cart->coupon);
        }

        $total = max(0, $subtotal - $discount);

        $cart->subtotal = $subtotal;
        $cart->total = $total;

        $cart->save();

        return $cart;
    }

    public function merge(?string $cartToken, User $user): void
    {
        if (!$cartToken) return;

        $guestCart = Cart::where('cart_token', $cartToken)->first();
        if (!$guestCart) return;

        $userCart = Cart::firstOrCreate([
            'user_id' => $user->id
        ]);

        foreach ($guestCart->items as $item) {
            $existing = $userCart->items()
                ->where('product_variant_id', $item->product_variant_id)
                ->first();

            if ($existing) {
                $existing->increment('quantity', $item->qty);
            } else {
                $userCart->items()->create([
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->qty,
                ]);
            }
        }

        $guestCart->delete();
    }
}
