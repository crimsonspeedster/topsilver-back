<?php
namespace App\Http\Controllers\Api\V1;

use App\Events\OneClickOrderCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderInOneClickRequest;
use App\Services\CheckoutService;

class BuyInOneClickController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService,
    ) {}

    public function __invoke(CreateOrderInOneClickRequest $request)
    {
        $lead = $this->checkoutService->createOrderInOneClick(
            $request->validated(),
        );

        OneClickOrderCreated::dispatch($lead);

        return response()->json([
            'message' => "Ми зв'яжемося з вами найближчим часом",
        ]);
    }
}
