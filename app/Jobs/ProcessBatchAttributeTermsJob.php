<?php
namespace App\Jobs;

use App\Enums\IntegrationBatchStatus;
use App\Enums\IntegrationErrorCode;
use App\Models\Attribute;
use App\Models\AttributeTerm;
use App\Models\IntegrationBatch;
use App\Models\IntegrationBatchError;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

class ProcessBatchAttributeTermsJob extends ProcessBatchJob
{
    protected function lockKey(): string
    {
        return 'attribute-terms-batch-import';
    }

    protected function updateChunk(array $items): array
    {
        $processed = 0;
        $failed = 0;

        $rows = [];

        $attributesMap = Attribute::pluck('id', 'external_id');

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

            $attributeId = $attributesMap[$item['attribute_id']] ?? null;

            if (!$attributeId) {
                $this->logError(
                    index: $index,
                    code: IntegrationErrorCode::InvalidValue->value,
                    message: 'Invalid attribute id',
                    field: 'attribute_id',
                    externalId: $item['id'] ?? null,
                );

                $failed++;

                continue;
            }

            $rows[] = [
                'external_id' => $item['id'],
                'attribute_id' => $attributeId,
                'title' => $item['title'],
                'slug' => Str::slug($item['title'] . '_' . $item['id']),
                'meta_value' => $item['meta_value'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $processed++;
        }

        if ($rows) {
            AttributeTerm::upsert(
                $rows,
                ['attribute_id', 'external_id'],
                ['title', 'slug', 'meta_value', 'updated_at']
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
            'title' => [
                'required' => true,
            ],
            'attribute_id' => [
                'required' => true,
            ],
        ];
    }
}
