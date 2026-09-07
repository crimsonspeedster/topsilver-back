<?php
namespace App\Jobs;

use App\Models\Bundle;

class ProcessBatchBundlePricesJob extends ProcessBatchJob
{
    protected function lockKey(): string
    {
        return 'bundles-prices-batch-' . $this->batch->id;
    }

    protected function chunkSize(): int
    {
        return 500;
    }

    protected function updateChunk(array $items): array
    {
        $processed = 0;
        $failed = 0;

        $rows = [];

        foreach ($items as $index => $item) {
            $errors = $this->validateItem($item);

            if ($errors) {
                $failed++;

                foreach ($errors as $error) {
                    $this->logError(
                        index: $index,
                        code: $error['code']->value,
                        message: $error['message'],
                        field: $error['field'],
                        externalId: $item['id'] ?? null,
                    );
                }

                continue;
            }

            $rows[] = [
                'external_id' => $item['id'],
                'price' => $item['price'],
                'old_price' => $item['old_price'] ?? null,
                'updated_at' => now(),
            ];

            $processed++;
        }

        if ($rows) {
            Bundle::upsert(
                $rows,
                ['external_id'],
                [
                    'price',
                    'old_price',
                    'updated_at',
                ]
            );
        }

        return [$processed, $failed];
    }

    protected function rules(): array
    {
        return [
            'id' => [
                'required' => true,
            ],
            'price' => [
                'required' => true,
            ],
        ];
    }
}
