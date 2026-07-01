<?php
namespace App\Http\Controllers\Api\V1\Integrations\OneC;

use App\Http\Controllers\Controller;
use App\Http\Resources\IntegrationBatchResource;
use App\Models\IntegrationBatch;

class BatchController extends Controller
{
    public function status(IntegrationBatch $batch)
    {
        $batch->loadMissing('errors');

        return response()->json([
            'data' => new IntegrationBatchResource($batch),
        ]);
    }
}
