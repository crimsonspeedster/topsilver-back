<?php
namespace App\Http\Controllers\Api\V1\Integrations\OneC;

use App\Enums\IntegrationBatchStatus;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessBatchCouponsJob;
use App\Models\Coupon;
use App\Models\IntegrationBatch;
use Illuminate\Http\Request;

class CouponsSyncController extends Controller
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
            'entity' => 'coupons',
            'status' => IntegrationBatchStatus::Pending,
            'items_count' => count($data),
            'payload' => $request->getContent(),
        ]);

        ProcessBatchCouponsJob::dispatch($batch);

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

        $ids = Coupon::whereIn('external_id', $externalIds)
            ->pluck('id');

        if ($ids->isEmpty()) {
            return response()->json([
                'deleted' => 0,
            ]);
        }

        Coupon::whereIn('id', $ids)->delete();

        return response()->json([
            'deleted' => $ids->count(),
        ]);
    }
}
