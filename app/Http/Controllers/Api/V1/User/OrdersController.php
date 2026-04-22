<?php
namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaginationResource;
use App\Models\Order;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $orders = $user
            ->orders()
            ->with([
                'items.product.sluggable',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return response()->json([
            'data' => [
                'orders' => OrderResource::collection($orders->items()),
                'pagination' => new PaginationResource($orders),
            ]
        ]);
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $order->load([
            'items.product.sluggable',
        ]);

        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }
}
