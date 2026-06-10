<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessTaxonomyParentsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $entityClass,
    ) {}

    public function handle(): void
    {
        $entities = $this->entityClass::query()
            ->select(['id', 'external_id', 'parent_external_id'])
            ->get();

        $map = $entities->pluck('id', 'external_id');

        foreach ($entities as $entity) {
            if (!$entity->parent_external_id) {
                continue;
            }

            $parentId = $map[$entity->parent_external_id] ?? null;

            if (!$parentId) {
                continue;
            }

            if ($entity->parent_id !== $parentId) {
                $entity->update([
                    'parent_id' => $parentId,
                ]);
            }
        }
    }
}
