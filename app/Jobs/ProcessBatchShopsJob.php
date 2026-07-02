<?php
namespace App\Jobs;

use App\Enums\EntityStatus;
use App\Enums\IntegrationBatchStatus;
use App\Enums\IntegrationErrorCode;
use App\Models\City;
use App\Models\IntegrationBatch;
use App\Models\IntegrationBatchError;
use App\Models\Shop;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ProcessBatchShopsJob implements ShouldQueue
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
        $lock = Cache::lock('shops-batch-import', 600);

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

            $data = json_decode($this->batch->payload, true);

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
            $shopIds = [];

            collect($items)
                ->chunk(200)
                ->each(function ($chunk) use (&$processed, &$failed, &$shopIds) {
                    [$p, $f, $ids] = $this->updateChunk(
                        $chunk->toArray()
                    );

                    $processed += $p;
                    $failed += $f;

                    $shopIds = array_merge(
                        $shopIds,
                        $ids
                    );
                });

            if (!empty($shopIds)) {
                GenerateEntityMetaJob::dispatch(
                    Shop::class,
                    array_unique($shopIds)
                )->onQueue('import_1c');
            }

            $this->batch->update([
                'status' => IntegrationBatchStatus::Completed,
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

    private function updateChunk(array $items): array
    {
        $processed = 0;
        $failed = 0;

        static $cities = null;

        if ($cities === null) {
            $cities = City::query()
                ->select(['id', 'city_code'])
                ->get()
                ->keyBy('city_code');
        }

        $now = now();

        $shopRows = [];
        $externalIds = [];

        foreach ($items as $index => $item) {
            $errors = $this->validateItem($item);

            if (!empty($errors)) {
                $failed++;

                foreach ($errors as $error) {
                    $this->logError(
                        index: $index,
                        code: $error['code']->value,
                        message: $error['message'],
                        field: $error['field'],
                        externalId: $item['id'] ?: null,
                    );
                }

                continue;
            }

            $cityCode = trim($item['city_code'] ?? '');
            $title = trim($item['title'] ?? '');
            $address = trim($item['address'] ?? '');
            $addressLink = trim($item['address_link'] ?? '');
            $phone = trim($item['phone'] ?? '');
            $timeWorking = trim($item['time_working'] ?? '');
            $externalID = trim($item['id'] ?? '');
            $shortDescription = trim($item['short_description'] ?? '');

            $city = $cities[$cityCode] ?? null;

            if (!$city) {
                $this->logError(
                    index: $index,
                    code: IntegrationErrorCode::InvalidValue->value,
                    message: 'City not found',
                    field: 'city_code',
                    externalId: $item['id'] ?: null,
                );

                $failed++;
                continue;
            }

            $shopRows[$externalID] = [
                'external_id' => $externalID,
                'title' => $title,
                'city_id' => $city->id,
                'address' => $address,
                'address_link' => $addressLink,
                'phone' => $phone,
                'time_working' => $timeWorking,
                'short_description' => $shortDescription ?: null,
                'status' => EntityStatus::Published,
                'published_at' => $now,
                'updated_at' => $now,
                'created_at' => $now,
            ];

            $externalIds[] = $externalID;

            $processed++;
        }

        if (!$shopRows) {
            $this->failBatch('Empty payload');

            return [$processed, $failed, []];
        }

        Shop::upsert(
            array_values($shopRows),
            ['external_id'],
            [
                'title',
                'city_id',
                'address',
                'address_link',
                'phone',
                'time_working',
                'short_description',
                'status',
                'published_at',
                'updated_at',
            ]
        );

        $shopIds = Shop::query()
            ->whereIn('external_id', $externalIds)
            ->pluck('id')
            ->all();

        return [
            $processed,
            $failed,
            $shopIds,
        ];
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
            'id' => [
                'required' => true,
            ],
            'title' => [
                'required' => true,
            ],
            'city_code' => [
                'required' => true,
            ],
            'address' => [
                'required' => true,
            ],
            'address_link' => [
                'required' => true,
            ],
            'phone' => [
                'required' => true,
            ],
            'time_working' => [
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
