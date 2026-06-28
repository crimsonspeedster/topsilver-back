<?php
namespace App\Http\Controllers\Api\V1\Integrations\OneC;

use App\Enums\IntegrationBatchStatus;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessBatchProductVariantsJob;
use App\Jobs\ProcessBatchProductVariantPricesJob;
use App\Jobs\ProcessBatchProductVariantStocksJob;
use App\Models\IntegrationBatch;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductVariantSyncController extends Controller
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
            'entity' => 'product_variants',
            'status' => IntegrationBatchStatus::Pending,
            'items_count' => count($data),
            'payload' => $request->getContent(),
        ]);

        ProcessBatchProductVariantsJob::dispatch($batch)->onQueue('import');

        return response()->json([
            'success' => true,
        ]);
    }

    public function price(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data)) {
            return response()->json([
                'message' => 'Empty payload'
            ], 422);
        }

        $batch = IntegrationBatch::create([
            'integration' => '1c',
            'entity' => 'product_variant_prices',
            'status' => IntegrationBatchStatus::Pending,
            'items_count' => count($data),
            'payload' => $request->getContent(),
        ]);

        ProcessBatchProductVariantPricesJob::dispatch($batch)->onQueue('import');

        return response()->json([
            'success' => true,
        ]);
    }

    public function stock(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data)) {
            return response()->json([
                'message' => 'Empty payload'
            ], 422);
        }

        $batch = IntegrationBatch::create([
            'integration' => '1c',
            'entity' => 'product_stock',
            'status' => IntegrationBatchStatus::Pending,
            'items_count' => count($data),
            'payload' => $request->getContent(),
        ]);

        ProcessBatchProductVariantStocksJob::dispatch($batch)->onQueue('import');

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

        $products = ProductVariant::query()
            ->whereIn('external_id', $externalIds)
            ->get(['id', 'external_id']);

        $foundExternalIds = $products->pluck('external_id')->toArray();
        $ids = $products->pluck('id')->toArray();

        if (!empty($ids)) {
            ProductVariant::whereIn('id', $ids)->delete();
        }

        $notFound = array_values(array_diff($externalIds, $foundExternalIds));

        return response()->json([
            'deleted' => $foundExternalIds,
            'not_found' => $notFound,
            'total_requested' => count($externalIds),
        ]);
    }
}
