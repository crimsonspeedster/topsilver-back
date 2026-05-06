<?php
namespace App\Http\Controllers\Api\V1\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartItemRequest;
use App\Http\Resources\CartResource;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartItemsController extends Controller
{
    public function __construct(
        protected CartService $cartService,
    ) {}

    public function store(StoreCartItemRequest $request)
    {
        $cart = $request->attributes->get('cart');
        $data = $request->validated();

        return DB::transaction(function () use ($cart, $data) {
            $variant = null;

            if (!empty($data['product_variant_id'])) {
                $variant = ProductVariant::with('product')
                    ->lockForUpdate()
                    ->findOrFail($data['product_variant_id']);

                $product = $variant->product;
            } else {
                $product = Product::lockForUpdate()
                    ->findOrFail($data['product_id']);
            }

            $availableStock = $this->cartService->getAvailableStock($product, $variant);

            $item = $this->cartService->resolveCartItem($cart, $product, $variant)
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
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'quantity' => $data['quantity'],
                    'price' => $product->price_on_sale ?? $product->price,
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
        $cart = $request->attributes->get('cart');

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        return DB::transaction(function () use ($cart, $id, $validated) {
            $item = CartItem::where('id', $id)
                ->where('cart_id', $cart->id)
                ->with(['product', 'variant'])
                ->lockForUpdate()
                ->firstOrFail();

            $product = $item->product;
            $variant = $item->variant;

            $availableStock = $this->cartService->getAvailableStock($product, $variant);

            if ($validated['quantity'] > $availableStock) {
                abort(422, 'Not enough stock available');
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
        $cart = $request->attributes->get('cart');

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
}
