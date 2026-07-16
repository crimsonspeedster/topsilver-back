<?php
namespace App\Http\Controllers\Api\V1\Integrations\OneC;

use App\Enums\IntegrationBatchStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\QuickOrderResource;
use App\Jobs\ProcessBatchOrderInOneClickStatusesJob;
use App\Models\IntegrationBatch;
use App\Models\OneClickRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class OneClickBuySyncController extends Controller
{
    public function show(Request $request)
    {
        $validated = $request->validate([
            'updated_from' => ['nullable', 'date_format:Y-m-d\TH:i:s\Z'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:500'],
            'status' => ['nullable', 'array'],
            'status.*' => ['required', new Enum(OrderStatus::class)],
        ]);

        $orders = OneClickRequest::query()
            ->with([
                'product.sluggable',
            ])
            ->when(
                $validated['updated_from'] ?? null,
                fn ($q, $date) => $q->where('updated_at', '>=', $date)
            )
            ->when(
                $validated['status'] ?? null,
                fn ($q, $statuses) => $q->whereIn('status', $statuses)
            )
            ->orderBy('updated_at')
            ->paginate($request->integer('per_page', 100));

        return response()->json([
            'data' => [
                'orders' => QuickOrderResource::collection($orders->items()),
                'pagination' => new PaginationResource($orders),
            ]
        ]);
    }

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
            'entity' => 'one_click_buy_order_statuses',
            'status' => IntegrationBatchStatus::Pending,
            'items_count' => count($data),
            'payload' => $request->getContent(),
        ]);

        ProcessBatchOrderInOneClickStatusesJob::dispatch($batch)->onQueue('import_1c');

        return response()->json([
            'job_id' => $batch->id,
        ]);
    }
}
