<?php
namespace App\Http\Controllers\Api\V1\Integrations\OneC;

use App\Enums\IntegrationBatchStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaginationResource;
use App\Jobs\ProcessBatchOrdersJob;
use App\Jobs\ProcessBatchOrderStatusesJob;
use App\Models\IntegrationBatch;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class OrdersGetController extends Controller
{
    public function show(Request $request)
    {
        $validated = $request->validate([
            'updated_from' => ['nullable', 'date_format:Y-m-d\TH:i:s\Z'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:500'],
            'status' => ['nullable', 'array'],
            'status.*' => ['required', new Enum(OrderStatus::class)],
        ]);

        $orders = Order::query()
            ->with('items')
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
                'orders' => OrderResource::collection($orders->items()),
                'pagination' => new PaginationResource($orders),
            ]
        ]);
    }

    public function rebuild(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data)) {
            return response()->json([
                'message' => 'Empty payload'
            ], 422);
        }

        $batch = IntegrationBatch::create([
            'integration' => '1c',
            'entity' => 'orders',
            'status' => IntegrationBatchStatus::Pending,
            'items_count' => count($data),
            'payload' => $request->getContent(),
        ]);

        ProcessBatchOrdersJob::dispatch($batch)->onQueue('import');

        return response()->json([
            'job_id' => $batch->id,
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
            'entity' => 'order_statuses',
            'status' => IntegrationBatchStatus::Pending,
            'items_count' => count($data),
            'payload' => $request->getContent(),
        ]);

        ProcessBatchOrderStatusesJob::dispatch($batch)->onQueue('import');

        return response()->json([
            'job_id' => $batch->id,
        ]);
    }
}
