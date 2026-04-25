<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethods;
use App\Enums\ShippingMethods;
use App\Models\Cart;
use App\Models\NPStreet;
use App\Models\NPWarehouse;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function checkout(Cart $cart, array $data): Order
    {
        return DB::transaction(function () use ($cart, $data) {
            $cart->load('items.product', 'items.variant');

            if ($cart->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Cart is empty',
                ]);
            }

            $subtotal = $this->calculateAndValidateStock($cart);

            [$shippingMethod, $shippingData] = $this->resolveShipping($data);
            [$paymentMethod, $paymentData, $status] = $this->resolvePayment($data);

            $order = $this->createOrder(
                $cart,
                $data,
                $subtotal,
                $status,
                $shippingMethod,
                $shippingData,
                $paymentMethod,
                $paymentData
            );

            $this->createOrderItemsAndUpdateStock($order, $cart);
            $this->clearCart($cart, $status);

            return $order;
        });
    }

    private function calculateAndValidateStock(Cart $cart): float
    {
        $subtotal = 0;
        $max = 999;

        foreach ($cart->items as $item) {
            $product = Product::where('id', $item->product_id)
                ->lockForUpdate()
                ->first();

            if (!$product) {
                throw ValidationException::withMessages([
                    'product' => "Product {$item->product_id} not found",
                ]);
            }

            $variant = null;

            if ($item->product_variant_id) {
                $variant = ProductVariant::where('id', $item->product_variant_id)
                    ->lockForUpdate()
                    ->first();

                if (!$variant) {
                    throw ValidationException::withMessages([
                        'variant' => "Variant {$item->product_variant_id} not found",
                    ]);
                }
            }

            $available = $this->getAvailableStock($product, $variant, $max);

            if ($item->quantity > $available) {
                throw ValidationException::withMessages([
                    'stock' => "Not enough stock for product {$product->id}",
                ]);
            }

            $price = $this->getItemPrice($product, $variant);
            $subtotal += $price * $item->quantity;
        }

        return $subtotal;
    }

    private function resolveShipping(array $data): array
    {
        $shippingMethod = ShippingMethod::findOrFail($data['shipping_method_id']);

        $shippingData = [
            'shipping_method_id' => $shippingMethod->id,
            'shipping_method_name' => $shippingMethod->name,
        ];

        switch ($shippingMethod->type) {
            case ShippingMethods::LOCAL_PICKUP:
                $shop = Shop::with(['city.region'])
                    ->findOrFail($data['shop_id']);

                $shippingData['shop_id'] = $data['shop_id'];
                $shippingData['region'] = $shop->city?->region?->name;
                $shippingData['city'] = $shop->city?->name;
                $shippingData['shop_name'] = $shop->name;
                $shippingData['shop_address'] = $shop->address;
                break;
            case ShippingMethods::NOVA_POSHTA_WAREHOUSE:
                $warehouse = NPWarehouse::query()
                    ->with(['city.area'])
                    ->where('ref', $data['np_warehouse_ref'])
                    ->firstOrFail();

                $shippingData['np_area'] = $warehouse->city?->area?->name;
                $shippingData['np_city'] = $warehouse->city?->name;
                $shippingData['np_warehouse'] = $warehouse->name;
                $shippingData['np_warehouse_address'] = $warehouse->address;
                $shippingData['np_warehouse_type'] = $warehouse->type;
                break;
            case ShippingMethods::NOVA_POSHTA_COURIER:
                $shippingData['np_city'] = $data['np_city'];
                $shippingData['np_street'] = $data['np_street'];
                $shippingData['np_house_number'] = $data['np_house_number'];
                $shippingData['np_apartment_number'] = $data['np_apartment_number'];
                break;
            default:
                break;
        }

        return [$shippingMethod, $shippingData];
    }

    private function resolvePayment(array $data): array
    {
        $paymentMethod = PaymentMethod::findOrFail($data['payment_method_id']);

        $paymentData = [
            'payment_method_id' => $paymentMethod->id,
            'payment_method_name' => $paymentMethod->name,
        ];

        $status = $paymentMethod->type === PaymentMethods::COD
            ? OrderStatus::CREATED
            : OrderStatus::PENDING_PAYMENT;

        return [$paymentMethod, $paymentData, $status];
    }

    private function createOrder(
        Cart $cart,
        array $data,
        float $subtotal,
        OrderStatus $status,
        $shippingMethod,
        array $shippingData,
        $paymentMethod,
        array $paymentData
    ): Order {
        return Order::create([
            'status' => $status,
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'paid_at' => null,
            'notes' => $data['notes'] ?? null,

            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,

            'shipping_type' => $shippingMethod->type,
            'shipping_data' => $shippingData,

            'payment_type' => $paymentMethod->type,
            'payment_data' => $paymentData,

            'user_id' => $cart->user_id,
        ]);
    }

    private function createOrderItemsAndUpdateStock(Order $order, Cart $cart): void
    {
        foreach ($cart->items as $item) {

            $product = $item->product;
            $variant = $item->variant;

            $price = $this->getItemPrice($product, $variant);

            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->title,
                'product_image' => $product->getFirstMediaUrl('main_image'),
                'product_price' => $price,
                'product_variant' => $variant?->toArray() ?? [],
                'quantity' => $item->quantity,
                'total' => $price * $item->quantity,
            ]);

            $this->decrementStock($product, $variant, $item->quantity);
        }
    }

    private function clearCart(Cart $cart, OrderStatus $status): void
    {
        if ($status === OrderStatus::CREATED) {
            $cart->items()->delete();
            $cart->delete();
        }
    }

    private function getItemPrice(Product $product, ?ProductVariant $variant): float
    {
        if ($variant) {
            return $variant->price_on_sale ?? $variant->price;
        }

        return $product->price_on_sale ?? $product->price;
    }

    private function getAvailableStock(Product $product, ?ProductVariant $variant, int $max): int
    {
        if ($variant) {
            return $product->manage_stock ? min($variant->stock, $max) : $max;
        }

        return $product->manage_stock ? min($product->stock, $max) : $max;
    }

    private function decrementStock(Product $product, ?ProductVariant $variant, int $qty): void
    {
        if (!$product->manage_stock) {
            return;
        }

        if ($variant) {
            $variant->decrement('stock', $qty);
        } else {
            $product->decrement('stock', $qty);
        }
    }
}
