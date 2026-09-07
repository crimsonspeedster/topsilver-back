<?php
namespace App\Jobs;

use App\Enums\AttributeTypes;
use App\Enums\IntegrationErrorCode;
use App\Models\Attribute;
use Illuminate\Support\Str;

class ProcessBatchAttributesJob extends ProcessBatchJob
{
    protected function lockKey(): string
    {
        return 'attributes-batch-import';
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
        ];
    }

    protected function updateChunk(array $items): array
    {
        $processed = 0;
        $failed = 0;

        $rows = [];

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
                        externalId: $item['id'] ?? null,
                    );
                }

                continue;
            }

            $type = $this->resolveType($item['type'] ?? null);

            if (!$type) {
                $failed++;

                $this->logError(
                    index: $index,
                    code: IntegrationErrorCode::InvalidValue->value,
                    message: 'Invalid type',
                    field: 'type',
                    externalId: $item['id'] ?? null,
                );

                continue;
            }

            $rows[] = [
                'external_id' => $item['id'],
                'title' => $item['title'],
                'slug' => Str::slug($item['title'] . '_' . $item['id']),
                'type' => $type,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $processed++;
        }

        if ($rows) {
            Attribute::upsert(
                $rows,
                ['external_id'],
                ['title', 'slug', 'type', 'updated_at']
            );
        }

        return [$processed, $failed];
    }

    private function resolveType(?string $type): ?AttributeTypes
    {
        if (!$type) {
            return AttributeTypes::Text;
        }

        return AttributeTypes::tryFrom($type);
    }
}
