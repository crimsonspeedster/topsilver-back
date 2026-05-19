<?php
namespace App\Http\Controllers\Api\V1\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartItemRequest;
use App\Http\Resources\CartResource;
use App\Models\Bundle;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartItemsController extends Controller
{
    public function __construct(
        protected CartService $cartService,
    ) {}

    public function store(StoreCartItemRequest $request)
    {
        $cart = $request->attributes->get('cart') ?? $this->cartService->getOrCreateCart($request);
        $data = $request->validated();

        return DB::transaction(function () use ($cart, $data) {
            $entity = $this->resolveEntity($data['entity_type'], $data['entity_id']);

            if (!$entity) {
                abort(404, 'Entity not found');
            }

            $variant = null;
            $availableStock = 999;

            if ($entity instanceof Product) {
                if (!empty($data['product_variant_id'])) {
                    $variant = ProductVariant::with('product')
                        ->lockForUpdate()
                        ->findOrFail($data['product_variant_id']);
                }

                $availableStock = $this->cartService->getAvailableStock($entity, $variant);
            }

            if ($entity instanceof Bundle) {
                if (!$entity->active) {
                    abort(422, 'This bundle is not available anymore');
                }
            }

            $item = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('entity_id', $entity->id)
                ->where('entity_type', $entity::class)
                ->when(
                    $data['product_variant_id'] ?? null,
                    fn ($q) => $q->where('product_variant_id', $data['product_variant_id']),
                    fn ($q) => $q->whereNull('product_variant_id')
                )
                ->lockForUpdate()
                ->first();

            $existingQty = $item?->quantity ?? 0;
            $newQty = $existingQty + $data['quantity'];

            if ($newQty > $availableStock) {
                abort(422, 'Not enough stock available');
            }

            if ($item) {
                $item->increment('quantity', $data['quantity']);
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'entity_id' => $entity->id,
                    'entity_type' => $entity::class,
                    'product_variant_id' => $variant?->id,
                    'quantity' => $data['quantity'],
                    'price' => $this->getEntityPrice($entity, $variant),
                ]);
            }

            $cart = $this->cartService->recalculateCart($cart);

            return response()->json([
                'data' => new CartResource(
                    $this->cartService->loadCartItems($cart),
                ),
            ]);
        });
    }

    public function update(int $id, Request $request)
    {
        $cart = $request->attributes->get('cart') ?? $this->cartService->getOrCreateCart($request);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        return DB::transaction(function () use ($cart, $id, $validated) {
            $item = CartItem::query()
                ->where('id', $id)
                ->where('cart_id', $cart->id)
                ->with(['entity'])
                ->lockForUpdate()
                ->firstOrFail();

            $entity = $item->entity;

            if (!$entity) {
                abort(404, 'Cart item entity not found');
            }

            if ($entity instanceof Product) {
                $availableStock = $this->cartService->getAvailableStock(
                    $entity,
                    $item->product_variant_id
                        ? ProductVariant::find($item->product_variant_id)
                        : null
                );

                if ($validated['quantity'] > $availableStock) {
                    abort(422, 'Not enough stock available');
                }
            }

            if ($entity instanceof Bundle) {
                if (!$entity->active) {
                    abort(422, 'This bundle is not available anymore');
                }
            }

            $item->update([
                'quantity' => $validated['quantity'],
            ]);

            $cart = $this->cartService->recalculateCart($cart);

            return response()->json([
                'data' => new CartResource(
                    $this->cartService->loadCartItems($cart),
                ),
            ]);
        });
    }

    public function destroy(int $id, Request $request)
    {
        $cart = $request->attributes->get('cart') ?? $this->cartService->getOrCreateCart($request);

        return DB::transaction(function () use ($cart, $id) {
            $item = CartItem::where('id', $id)
                ->where('cart_id', $cart->id)
                ->lockForUpdate()
                ->firstOrFail();

            $item->delete();

            $cart = $this->cartService->recalculateCart($cart);

            return response()->json([
                'data' => new CartResource(
                    $this->cartService->loadCartItems($cart),
                ),
            ]);
        });
    }

    private function resolveEntity(string $type, int $id): ?Model
    {
        return match ($type) {
            'product' => Product::find($id),
            'bundle' => Bundle::find($id),
            default => null,
        };
    }

    private function getEntityPrice(Model $entity, ?ProductVariant $variant = null): float
    {
        if ($entity instanceof Product) {
            return $variant?->price_on_sale ?? $variant?->price ?? $entity->price_on_sale ?? $entity->price;
        }

        if ($entity instanceof Bundle) {
            return $entity->price;
        }

        return 0;
    }
}
