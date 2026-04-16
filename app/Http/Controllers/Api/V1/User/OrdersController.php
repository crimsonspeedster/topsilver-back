<?php
namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $orders = $user
            ->orders()
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return response()->json([
            'data' => [
                'orders' => OrderResource::collection($orders->load('items.product')),
                'pagination' => [
                    'total_pages' => $orders->lastPage(),
                    'current_page' => $orders->currentPage(),
                    'per_page' => $orders->perPage(),
                    'total_items' => $orders->total(),
                    'has_more_pages' => $orders->hasMorePages(),
                ],
            ]
        ]);
    }

    public function show(Request $request, int $id)
    {
        $user = $request->user();
        $order = $user->orders()
            ->with(['items.product'])
            ->findOrFail($id);

        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }
}
