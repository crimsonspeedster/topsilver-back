<?php
namespace App\Http\Controllers\Api\V1\Integrations\OneC;

use App\Enums\IntegrationBatchStatus;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessBatchBonusesJob;
use App\Models\Bonus;
use App\Models\IntegrationBatch;
use Illuminate\Http\Request;

class BonusesSyncController extends Controller
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
            'entity' => 'bonuses',
            'status' => IntegrationBatchStatus::Pending,
            'items_count' => count($data),
            'payload' => $request->getContent(),
        ]);

        ProcessBatchBonusesJob::dispatch($batch);

        return response()->json([
            'success' => true,
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

        $ids = Bonus::whereIn('external_id', $externalIds)
            ->pluck('id');

        if ($ids->isEmpty()) {
            return response()->json([
                'deleted' => 0,
            ]);
        }

        Bonus::whereIn('id', $ids)->delete();

        return response()->json([
            'deleted' => $ids->count(),
        ]);
    }
}
