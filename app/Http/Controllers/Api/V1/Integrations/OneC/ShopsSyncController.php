<?php
namespace App\Http\Controllers\Api\V1\Integrations\OneC;

use App\Enums\IntegrationBatchStatus;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessBatchShopsJob;
use App\Models\IntegrationBatch;
use App\Models\Seo;
use App\Models\Shop;
use App\Models\Slug;
use Illuminate\Http\Request;

class ShopsSyncController extends Controller
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
            'entity' => 'shops',
            'status' => IntegrationBatchStatus::Pending,
            'items_count' => count($data),
            'payload' => $request->getContent(),
        ]);

        ProcessBatchShopsJob::dispatch($batch)->onQueue('import');

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

        $shops = Shop::whereIn('external_id', $externalIds)->get(['id', 'external_id']);

        $foundExternalIds = $shops->pluck('external_id')->toArray();
        $shopIds = $shops->pluck('id')->toArray();

        if (!empty($shopIds)) {
            Shop::whereIn('id', $shopIds)->delete();

            Slug::where('entity_type', Shop::class)
                ->whereIn('entity_id', $shopIds)
                ->delete();

            Seo::where('entity_type', Shop::class)
                ->whereIn('entity_id', $shopIds)
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
