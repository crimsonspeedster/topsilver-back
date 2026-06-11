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

            if (!is_array($data) || empty($data)) {
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
                ->chunk(200)
                ->each(function ($chunk) use (&$processed, &$failed) {
                    [$p, $f] = $this->updateChunk($chunk->toArray());
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

    private function updateChunk(array $items): array
    {
        $processed = 0;
        $failed = 0;

        static $cities = null;

        if ($cities === null) {
            $cities = City::all()->keyBy('city_code');
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
            return [$processed, $failed];
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

        $shopMap = Shop::whereIn('external_id', $externalIds)
            ->pluck('id', 'external_id');

        $existingAllSlugs = Slug::query()
            ->pluck('slug')
            ->toArray();

        $existingSlugsInModel = Slug::where('entity_type', Shop::class)
            ->pluck('entity_id')
            ->flip()
            ->toArray();

        $existingSeo = Seo::where('entity_type', Shop::class)
            ->pluck('entity_id')
            ->flip()
            ->toArray();

        $slugService = app(SlugGenerateService::class);
        $seoService = app(SeoGenerateService::class);

        $slugRows = [];
        $seoRows = [];

        foreach ($shopRows as $externalId => $row) {
            $shopId = $shopMap[$externalId] ?? null;

            if (!$shopId) {
                continue;
            }

            if (!isset($existingSlugsInModel[$shopId])) {
                $slug = $slugService->generate(
                    $row['title'],
                    $existingAllSlugs
                );

                $slugRows[] = [
                    'slug' => $slug,
                    'entity_type' => Shop::class,
                    'entity_id' => $shopId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!isset($existingSeo[$shopId])) {
                $seo = $seoService->generateSeo(
                    $row['title'],
                    $row['short_description'] ?? $row['title'],
                    null
                );

                $seoRows[] = [
                    'entity_type' => Shop::class,
                    'entity_id' => $shopId,
                    'title' => $seo['title'],
                    'description' => $seo['description'],
                    'keywords' => $seo['keywords'],
                    'robots' => $seo['robots'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $existingSeo[$shopId] = true;
            }
        }

        if ($slugRows) {
            Slug::upsert(
                $slugRows,
                ['entity_type', 'entity_id'],
                ['slug', 'updated_at']
            );
        }

        if ($seoRows) {
            Seo::upsert(
                $seoRows,
                ['entity_type', 'entity_id'],
                ['title', 'description', 'keywords', 'robots', 'updated_at']
            );
        }

        return [$processed, $failed];
    }

    private function failBatch(string $message): void
    {
        $this->batch->update([
            'status' => IntegrationBatchStatus::Failed,
            'error_message' => $message,
        ]);
    }
}
