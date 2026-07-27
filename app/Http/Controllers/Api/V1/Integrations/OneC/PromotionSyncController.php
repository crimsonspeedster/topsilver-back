<?php
namespace App\Http\Controllers\Api\V1\Integrations\OneC;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\Seo;
use App\Models\Slug;
use Illuminate\Http\Request;
use App\Models\IntegrationBatch;
use App\Enums\IntegrationBatchStatus;
use App\Jobs\ProcessBatchPromotionsJob;

class PromotionSyncController extends Controller
{
    public function update(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data)) {
            return response()->json([
                'message' => 'Empty payload'
            ], 422);
        }

        $batch = IntegrationBatch::create([
            'integration' => '1c',
            'entity' => 'promotions',
            'status' => IntegrationBatchStatus::Pending,
            'items_count' => count($data),
            'payload' => $request->getContent(),
        ]);

        ProcessBatchPromotionsJob::dispatch($batch)->onQueue('import_1c');

        return response()->json([
            'job_id' => $batch->id,
        ]);
    }

    public function delete(Request $request)
    {
        $externalIds = $request->input('ids', []);

        if (empty($externalIds)) {
            return response()->json([
                'message' => 'Empty ids'
            ], 422);
        }

        $models = Promotion::query()
            ->whereIn('external_id', $externalIds)
            ->get(['id', 'external_id']);

        $foundExternalIds = $models->pluck('external_id')->toArray();
        $entityIds = $models->pluck('id')->toArray();

        if (!empty($entityIds)) {
            Promotion::whereIn('id', $entityIds)->delete();

            Slug::where('entity_type', Promotion::class)
                ->whereIn('entity_id', $entityIds)
                ->delete();

            Seo::where('entity_type', Promotion::class)
                ->whereIn('entity_id', $entityIds)
                ->delete();
        }

        $notFound = array_values(array_diff($externalIds, $foundExternalIds));

        return response()->json([
            'deleted' => $foundExternalIds,
            'not_found' => $notFound,
            'total_requested' => count($externalIds),
        ]);
    }
}
