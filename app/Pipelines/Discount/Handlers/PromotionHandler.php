<?php
namespace App\Pipelines\Discount\Handlers;

use App\Enums\PromotionTypes;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Promotion;
use App\Pipelines\Discount\Context\CartDiscountContext;
use App\Pipelines\Discount\Interfaces\DiscountHandler;
use Closure;
use Illuminate\Support\Collection;

class PromotionHandler implements DiscountHandler
{
    public function handle(
        CartDiscountContext $context,
        Closure $next
    ): CartDiscountContext {
        $cart = $context->cart;

        $cart->items()->update([
            'promotion_discount' => 0,
        ]);

        $cart->load('items.entity', 'items.variant');

        $productIds = $cart->items
            ->where('entity_type', Product::class)
            ->pluck('entity_id');

        if ($productIds->isEmpty()) {
            return $next($context);
        }

        $promotions = Promotion::query()
            ->whereHas('products', function ($query) use ($productIds) {
                $query->whereIn('products.id', $productIds);
            })
            ->with('products')
            ->get();

        foreach ($promotions as $promotion) {
            $items = $cart->items
                ->where('entity_type', Product::class)
                ->filter(function (CartItem $item) use ($promotion) {
                    return $promotion->products
                        ->contains('id', $item->entity_id);
                });

            match ($promotion->type) {
                PromotionTypes::ONE_PLUS_ONE_EQUALS_THREE => $this->applyOnePlusOneEqualsThree($items),
                default => null,
            };
        }

        return $next($context);
    }

    private function applyOnePlusOneEqualsThree(Collection $items): void
    {
        $products = collect();

        foreach ($items as $item) {
            for ($i = 0; $i < $item->quantity; $i++) {
                $products->push([
                    'item' => $item,
                    'price' => $item->price,
                ]);
            }
        }

        $products = $products
            ->sortBy('price')
            ->values();

        $groups = intdiv($products->count(), 3);

        if ($groups === 0) {
            return;
        }

        for ($i = 0; $i < $groups; $i++) {
            $freeProduct = $products[$i * 3];

            /** @var CartItem $item */
            $item = $freeProduct['item'];

            $discount = $freeProduct['price'] - 1;

            $item->promotion_discount += $discount;
            $item->save();
        }
    }
}
