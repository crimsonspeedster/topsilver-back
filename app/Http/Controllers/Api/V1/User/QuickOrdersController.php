<?php
namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\QuickOrderResource;
use Illuminate\Http\Request;

class QuickOrdersController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $orders = $user
            ->quickOrders()
            ->with([
                'product.sluggable',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => [
                'orders' => QuickOrderResource::collection($orders->items()),
                'pagination' => new PaginationResource($orders),
            ]
        ]);
    }
}
