<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;

class PaymentMethodsController extends Controller
{
    public function __invoke()
    {
        $methods = PaymentMethod::active()
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => PaymentMethodResource::collection($methods),
        ]);
    }
}
