<?php
namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class OrderSingleController extends Controller
{
    public function __invoke(Request $request, int $id)
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
