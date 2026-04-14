<?php
namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class OrdersController extends Controller
{
    public function __invoke(Request $request)
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
                ],
            ]
        ]);
    }
}
