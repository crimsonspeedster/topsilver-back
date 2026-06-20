<?php
namespace App\Jobs;

use App\Enums\EntityStatus;
use App\Enums\IntegrationBatchStatus;
use App\Models\City;
use App\Models\IntegrationBatch;
use App\Models\Seo;
use App\Models\Shop;
use App\Models\Slug;
use App\Services\SeoGenerateService;
use App\Services\SlugGenerateService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class ProcessBatchShopsJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public IntegrationBatch $batch
    ) {}

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
                'processed_at' => now(),
            ]);

            $data = json_decode($this->batch->payload, true);

            if (!is_array($data) || empty($data['items'])) {
                $this->failBatch('Empty payload');
                return;
            }

            $processed = 0;
            $failed = 0;
            $shopIds = [];

            collect($data['items'])
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
                )->onQueue('import');
            }

            $this->batch->update([
                'status' => IntegrationBatchStatus::Completed,
                'processed_count' => $processed,
                'failed_count' => $failed,
            ]);

        } finally {
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

        foreach ($items as $item) {

            $cityCode = trim($item['city_code'] ?? '');
            $title = trim($item['title'] ?? '');
            $address = trim($item['address'] ?? '');
            $addressLink = trim($item['address_link'] ?? '');
            $phone = trim($item['phone'] ?? '');
            $timeWorking = trim($item['time_working'] ?? '');
            $externalID = trim($item['id'] ?? '');
            $shortDescription = trim($item['short_description'] ?? '');

            if (
                $externalID === '' ||
                $cityCode === '' ||
                $title === '' ||
                $address === '' ||
                $addressLink === '' ||
                $phone === '' ||
                $timeWorking === ''
            ) {
                $failed++;
                continue;
            }

            $city = $cities[$cityCode] ?? null;

            if (!$city) {
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
        ]);
    }
}
