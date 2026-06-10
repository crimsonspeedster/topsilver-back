<?php
namespace App\Http\Controllers\Api\V1\Integrations\OneC;

use App\Enums\IntegrationBatchStatus;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessBatchTaxonomiesJob;
use App\Models\Category;
use App\Models\Collection;
use App\Models\IntegrationBatch;
use App\Models\Promotion;
use App\Models\Seo;
use App\Models\Slug;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TaxonomySyncController extends Controller
{
    public function update(Request $request, string $entity)
    {
        $modelClass = $this->resolveEntity($entity);
        $data = json_decode($request->getContent(), true);

        if (empty($data)) {
            return response()->json([
                'message' => 'Empty payload'
            ], 422);
        }

        $batch = IntegrationBatch::create([
            'integration' => '1c',
            'entity' => $entity,
            'status' => IntegrationBatchStatus::Pending,
            'items_count' => count($data),
            'payload' => $request->getContent(),
        ]);

        ProcessBatchTaxonomiesJob::dispatch($batch, $modelClass);

        return response()->json([
            'success' => true,
        ]);
    }

    public function delete(Request $request, string $entity)
    {
        $modelClass = $this->resolveEntity($entity);
        $externalIds = $request->input('ids', []);

        if (empty($externalIds)) {
            return response()->json([
                'message' => 'Empty ids'
            ], 422);
        }

        $entityIds = $modelClass::whereIn('external_id', $externalIds)
            ->pluck('id');

        if ($entityIds->isEmpty()) {
            return response()->json([
                'deleted' => 0,
            ]);
        }

        $modelClass::whereIn('id', $entityIds)->delete();

        Slug::where('entity_type', $modelClass)
            ->whereIn('entity_id', $entityIds)
            ->delete();

        Seo::where('entity_type', $modelClass)
            ->whereIn('entity_id', $entityIds)
            ->delete();

        return response()->json([
            'deleted' => $entityIds->count(),
        ]);
    }

    private function resolveEntity(string $entity): string
    {

        return match ($entity) {
            'categories' => Category::class,
            'collections' => Collection::class,
            'promotions' => Promotion::class,

            default => throw ValidationException::withMessages([
                'entity' => 'Unsupported entity'
            ]),
        };
    }
}
