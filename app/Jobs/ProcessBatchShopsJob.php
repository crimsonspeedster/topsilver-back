<?php
namespace App\Jobs;

use App\Enums\EntityStatus;
use App\Enums\IntegrationBatchStatus;
use App\Models\City;
use App\Models\IntegrationBatch;
use App\Models\Shop;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessBatchShopsJob implements ShouldQueue
{
    use Dispatchable;
    use Queueable;
    use InteractsWithQueue;
    use SerializesModels;

    public function __construct(
        public IntegrationBatch $batch
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        $this->batch->update([
            'status' => IntegrationBatchStatus::Processing,
            'processed_count' => 0,
            'failed_count' => 0,
        ]);

        try {
            $data = json_decode($this->batch->payload, true);

            if (!is_array($data) || empty($data)) {
                $this->failBatch('Empty payload');
                return;
            }

            $processed = 0;
            $failed = 0;

            collect($data)
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
        } catch (Throwable $exception) {
            $this->batch->update([
                'status' => IntegrationBatchStatus::Failed,
                'error_message' => $exception->getMessage(),
            ]);

            throw $exception;
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

        foreach ($items as $item) {
            $cityCode = trim($item['city_code'] ?? '');
            $title = trim($item['title'] ?? '');
            $address = trim($item['address'] ?? '');
            $addressLink = trim($item['address_link'] ?? '');
            $phone = trim($item['phone'] ?? '');
            $timeWorking = trim($item['time_working'] ?? '');
            $externalID = trim($item['external_id'] ?? '');
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

            try {
                Shop::updateOrCreate(
                    [
                        'external_id' => $externalID,
                    ],
                    [
                        'title' => $title,
                        'city_id' => $city->id,
                        'address' => $address,
                        'short_description' => isset($item['short_description'])
                            ? $shortDescription
                            : null,
                        'address_link' => $addressLink,
                        'phone' => $phone,
                        'time_working' => $timeWorking,
                        'status' => EntityStatus::Published,
                        'published_at' => now(),
                    ]
                );

                $processed++;
            } catch (Throwable $e) {
                $failed++;
            }
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
