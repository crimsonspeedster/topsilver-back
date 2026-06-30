<?php
namespace App\Jobs;

use App\Enums\IntegrationBatchStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethods;
use App\Enums\ShippingMethods;
use App\Models\Bundle;
use App\Models\IntegrationBatch;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Exception;

class ProcessBatchOrdersJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public IntegrationBatch $batch
    ) {}

    public function handle(): void
    {
        $lock = Cache::lock('orders-batch-import', 600);

        if (!$lock->get()) {
            return;
        }

        try {
            $this->batch->update([
                'status' => IntegrationBatchStatus::Processing,
                'processed_count' => 0,
                'failed_count' => 0,
                'processed_at' => now(),
            ]);

            $data = is_string($this->batch->payload)
                ? json_decode($this->batch->payload, true)
                : $this->batch->payload;

            if (!is_array($data) || empty($data['items'])) {
                $this->failBatch('Empty payload');
                return;
            }

            $processed = 0;
            $failed = 0;

            collect($data['items'])
                ->chunk(100)
                ->each(function ($chunk) use (&$processed, &$failed) {
                    [$p, $f] = $this->processChunk($chunk->toArray());

                    $processed += $p;
                    $failed += $f;
                });

            $this->batch->update([
                'status' => IntegrationBatchStatus::Completed,
                'processed_count' => $processed,
                'failed_count' => $failed,
            ]);

        } finally {
            optional($lock)->release();
        }
    }

    private function processChunk(array $orders): array
    {
        $processed = 0;
        $failed = 0;

        $now = now();

        foreach ($orders as $orderData) {
            try {
                if (
                    empty($orderData['public_token']) ||
                    empty($orderData['status']) ||
                    empty($orderData['first_name']) ||
                    empty($orderData['last_name']) ||
                    empty($orderData['middle_name']) ||
                    empty($orderData['phone']) ||
                    empty($orderData['payment_type']) ||
                    empty($orderData['payment_data']) ||
                    empty($orderData['shipping_type']) ||
                    empty($orderData['shipping_data']) ||
                    empty($orderData['items'])
                ) {
                    $failed++;
                    continue;
                }

                $order = Order::where('public_token', $orderData['public_token'])->first();

                if (!$order) {
                    $failed++;
                    continue;
                }

                $status = OrderStatus::tryFrom($orderData['status']);
                $payment_type = PaymentMethods::tryFrom($orderData['payment_type']);
                $shipping_type = ShippingMethods::tryFrom($orderData['shipping_type']);

                $subtotal = floatval($orderData['subtotal']);
                $total = floatval($orderData['total']);
                $discount_amount = floatval($orderData['discount_amount']);

                $is_shipping_data_valid = $this->validateShippingData($orderData['shipping_data']);
                $is_payment_data_valid = $this->validatePaymentData($orderData['payment_data']);

                if (!$status || !$payment_type || !$shipping_type || !$subtotal || !$total || !$is_shipping_data_valid || !$is_payment_data_valid) {
                    $failed++;
                    continue;
                }

                DB::transaction(function () use ($order, $orderData, $status, $payment_type, $shipping_type, $subtotal, $total, $discount_amount) {
                    $order->update([
                        'status' => $status,

                        'subtotal' => $subtotal,
                        'total' => $total,
                        'discount_amount' => $discount_amount,

                        'coupon_code' => $orderData['coupon_code'] ?? null,

                        'paid_at' => $orderData['paid_at'] ?? null,

                        'notes' => $orderData['notes'] ?? null,

                        'first_name' => $orderData['first_name'] ?? null,
                        'last_name' => $orderData['last_name'] ?? null,
                        'middle_name' => $orderData['middle_name'] ?? null,
                        'phone' => $orderData['phone'] ?? null,
                        'email' => $orderData['email'] ?? null,

                        'payment_type' => $payment_type,
                        'payment_data' => $orderData['payment_data'],

                        'shipping_type' => $shipping_type,
                        'shipping_data' => $orderData['shipping_data']
                    ]);

                    if (!empty($orderData['items'])) {
                        $order->items()->delete();

                        foreach ($orderData['items'] as $item) {
                            $entity = $this->resolveEntity(
                                $item['entity_type'] ?? null,
                                $item['id'] ?? null
                            );

                            $variant = $this->resolveVariant(
                                $item['product_variant'] ?? null
                            );

                            if (
                                !$entity ||
                                empty($item['total']) ||
                                empty($item['price'])
                            ) {
                                throw new Exception(
                                    "Invalid order item: {$item['id']}"
                                );
                            }

                            $variant_attributes = [];

                            if ($variant) {
                                foreach ($variant->attributeTerms as $attributeTerm) {
                                    $variant_attributes[] = [
                                        'attribute_name' => $attributeTerm->attribute->title,
                                        'attribute_value' => $attributeTerm->title,
                                    ];
                                }
                            }

                            $order->items()->create([
                                'external_id' => $item['id'],

                                'entity_id' => $entity->id,
                                'entity_name' => $entity->title,
                                'entity_type' => $item['entity_type'] ?? 'product',

                                'entity_image' => $entity?->getFirstMediaUrl('media') ?? null,
                                'entity_price' => floatval($item['price']),

                                'quantity' => (int) $item['quantity'] ?? 1,
                                'total' => $item['total'],

                                'product_variant' => $variant ? [
                                    'external_id' => $variant->external_id,
                                    'attributes' => $variant_attributes,
                                ] : [],
                            ]);
                        }
                    }
                });

                $processed++;
            } catch (\Throwable $e) {
                $failed++;
                report($e);
            }
        }

        return [$processed, $failed];
    }

    private function resolveEntity(?string $type, ?string $externalId): Bundle|Product|null
    {
        if (!$type || !$externalId) {
            return null;
        }

        return match ($type) {
            'product' => Product::where('external_id', $externalId)->first(),
            'bundle'  => Bundle::where('external_id', $externalId)->first(),
            default   => null,
        };
    }

    private function resolveVariant(?string $externalId): ?ProductVariant
    {
        if (!$externalId) {
            return null;
        }

        return ProductVariant::where('external_id', $externalId)->first();
    }

    private function validatePaymentData(array $data): bool
    {
        return
            isset($data['payment_method_id']) &&
            isset($data['payment_method_name']);
    }

    private function validateShippingData(array $data): bool
    {
        if (!$this->validateShippingBase($data)) {
            return false;
        }

        return match ($data['shipping_method_type']) {
            'local_pickup' => $this->validateLocalPickup($data),
            'nova_poshta_courier' => $this->validateNovaPoshtaCourier($data),
            'nova_poshta_warehouse' => $this->validateNovaPoshtaWarehouse($data),
            default => false,
        };
    }

    private function validateShippingBase(array $data): bool
    {
        return
            isset($data['shipping_method_id']) &&
            isset($data['shipping_method_name']) &&
            isset($data['shipping_method_type']);
    }

    private function validateLocalPickup(array $data): bool
    {
        return
            isset($data['external_id']) &&
            isset($data['shop_address']) &&
            isset($data['shop_link']) &&
            isset($data['shop_phone']);
    }

    private function validateNovaPoshtaCourier(array $data): bool
    {
        return
            isset($data['np_area']) &&
            isset($data['np_city']) &&
            isset($data['np_warehouse']) &&
            isset($data['np_warehouse_type']) &&
            isset($data['np_warehouse_address']);
    }

    private function validateNovaPoshtaWarehouse(array $data): bool
    {
        return
            isset($data['np_street_ref']) &&
            isset($data['np_street_name']) &&
            isset($data['np_locality_ref']) &&
            isset($data['np_locality_name']) &&
            isset($data['np_house_number']);
    }

    private function failBatch(string $message): void
    {
        $this->batch->update([
            'status' => IntegrationBatchStatus::Failed,
            'error_message' => $message,
        ]);
    }
}
