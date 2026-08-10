<?php
namespace App\Jobs;

use App\Enums\IntegrationBatchStatus;
use App\Enums\IntegrationErrorCode;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethods;
use App\Enums\ProductTypes;
use App\Enums\ShippingMethods;
use App\Models\Bundle;
use App\Models\Certificate;
use App\Models\IntegrationBatch;
use App\Models\IntegrationBatchError;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Exception;
use Throwable;

class ProcessBatchOrdersJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public IntegrationBatch $batch
    ) {}

    /**
     * @throws Throwable
     */
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
                'started_at' => now(),
            ]);

            $data = is_string($this->batch->payload)
                ? json_decode($this->batch->payload, true)
                : $this->batch->payload;

            if (!is_array($data) || empty($data['items'])) {
                $this->failBatch('Empty payload');
                return;
            }

            $items = $data['items'];

            if (!is_array($items) || empty($items)) {
                $this->failBatch('Empty payload');
                return;
            }

            $processed = 0;
            $failed = 0;

            collect($items)
                ->chunk(100)
                ->each(function ($chunk) use (&$processed, &$failed) {
                    [$p, $f] = $this->processChunk($chunk->toArray());

                    $processed += $p;
                    $failed += $f;
                });

            $status = $failed > 0 ? IntegrationBatchStatus::PartialFailed : IntegrationBatchStatus::Completed;

            $this->batch->update([
                'status' => $status,
                'processed_count' => $processed,
                'failed_count' => $failed,
                'finished_at' => now(),
                'items_count' => count($items),
            ]);

        }
        catch (Throwable $e) {
            $this->failBatch($e->getMessage());

            throw $e;
        }
        finally {
            optional($lock)->release();
        }
    }

    private function processChunk(array $orders): array
    {
        $processed = 0;
        $failed = 0;

        $now = now();

        foreach ($orders as $index => $orderData) {
            $errors = $this->validateItem($orderData);

            if (!empty($errors)) {
                $failed++;

                foreach ($errors as $error) {
                    $this->logError(
                        index: $index,
                        code: $error['code']->value,
                        message: $error['message'],
                        field: $error['field'],
                        externalId: $orderData['public_token'] ?: null,
                    );
                }

                continue;
            }

            $order = Order::where('public_token', $orderData['public_token'])->first();

            if (!$order) {
                $failed++;

                $this->logError(
                    index: $index,
                    code: IntegrationErrorCode::InvalidValue->value,
                    message: 'Not found Order',
                    externalId: $orderData['public_token'],
                );

                continue;
            }

            $status = OrderStatus::tryFrom($orderData['status'] ?? '');
            $payment_type = PaymentMethods::tryFrom($orderData['payment_type'] ?? '');
            $shipping_type = ShippingMethods::tryFrom($orderData['shipping_type'] ?? '');

            $subtotal = floatval($orderData['subtotal']);
            $total = floatval($orderData['total']);
            $discount_amount = floatval($orderData['discount_amount']);

            $is_shipping_data_valid = $this->validateShippingData($orderData['shipping_data']);

            if (!$status || !$payment_type || !$shipping_type || !$subtotal || !$total || !$is_shipping_data_valid) {
                $failed++;

                $this->logError(
                    index: $index,
                    code: IntegrationErrorCode::InvalidValue->value,
                    message: 'Invalid order data',
                    externalId: $orderData['public_token'],
                );

                continue;
            }

            $payment_method = PaymentMethod::where('type', $payment_type)
                ->first();

            $shipping_method = ShippingMethod::where('type', $shipping_type)
                ->first();

            if (!$payment_method || !$shipping_method) {
                $failed++;

                $this->logError(
                    index: $index,
                    code: IntegrationErrorCode::InvalidValue->value,
                    message: 'Invalid payment or shipping data',
                    externalId: $orderData['public_token'],
                );

                continue;
            }

            $payment_data = [
                'payment_method_id' => $payment_method->id,
                'payment_method_name' => $payment_method->name,
                'payment_method_type' => $payment_method->type->value,
            ];

            $shipping_data = $orderData['shipping_data'];
            $shipping_data['shipping_method_id'] = $shipping_method->id;
            $shipping_data['shipping_method_name'] = $shipping_method->name;
            $shipping_method['shipping_method_type'] = $shipping_method->type->value;

            DB::transaction(function () use ($order, $orderData, $status, $payment_type, $shipping_type, $subtotal, $total, $discount_amount, $payment_data, $shipping_data) {
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
                    'payment_data' => $payment_data,

                    'shipping_type' => $shipping_type,
                    'shipping_data' => $shipping_data,
                ]);

                if (!empty($orderData['items'])) {
                    $order->items()->delete();

                    foreach ($orderData['items'] as $item) {
                        $entity = $this->resolveEntity(
                            $item['entity_type'] ?? null,
                            $item['id'] ?? null
                        );

                        $variant = $this->resolveVariant(
                            $item['product_variant']['id'] ?? null
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

                        if ($entity instanceof Product && $entity->type === ProductTypes::VARIABLE && !$variant) {
                            throw new Exception(
                                "Invalid variant in item: {$item['id']}"
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
                                'id' => $variant->id,
                                'external_id' => $variant->external_id,
                                'attributes' => $variant_attributes,
                            ] : [],
                        ]);
                    }
                }

                if (!empty($orderData['certificates'])) {
                    $order->certificates()->detach();

                    foreach ($orderData['certificates'] as $item) {
                        $certificate = Certificate::where('external_id', $item['id'])->first();

                        if (!$certificate) {
                            throw new Exception(
                                "Invalid certificate: {$item['id']}"
                            );
                        }

                        $order->certificates()->attach($certificate->id);
                    }
                }
            });

            $processed++;
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

    private function validateShippingData(array $data): bool
    {
        return match ($data['shipping_method_type']) {
            'local_pickup' => $this->validateLocalPickup($data),
            'nova_poshta_courier' => $this->validateNovaPoshtaCourier($data),
            'nova_poshta_warehouse' => $this->validateNovaPoshtaWarehouse($data),
            default => false,
        };
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
            'finished_at' => now(),
        ]);
    }

    private function logError(
        int $index,
        string $code,
        string $message,
        ?string $field = null,
        ?string $externalId = null,
    ): void {
        IntegrationBatchError::create([
            'integration_batch_id' => $this->batch->id,
            'item_index' => $index,
            'external_id' => $externalId,
            'field' => $field,
            'code' => $code,
            'message' => $message,
        ]);
    }

    private function rules(): array
    {
        return [
            'public_token' => [
                'required' => true,
            ],
            'status' => [
                'required' => true,
            ],
            'first_name' => [
                'required' => true,
            ],
            'last_name' => [
                'required' => true,
            ],
            'middle_name' => [
                'required' => true,
            ],
            'phone' => [
                'required' => true,
            ],
            'payment_type' => [
                'required' => true,
            ],
            'payment_data' => [
                'required' => true,
            ],
            'shipping_type' => [
                'required' => true,
            ],
            'shipping_data' => [
                'required' => true,
            ],
            'items' => [
                'required' => true,
            ],
        ];
    }

    private function validateItem(array $item): array
    {
        $rules = $this->rules();
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $item[$field] ?? null;

            $valueStr = is_string($value) ? trim($value) : $value;

            if (($fieldRules['required'] ?? false) && empty($valueStr)) {
                $errors[] = [
                    'field' => $field,
                    'code' => IntegrationErrorCode::Required,
                    'message' => ucfirst($field) . ' is required',
                ];
            }
        }

        return $errors;
    }
}
