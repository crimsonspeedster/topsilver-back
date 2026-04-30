<?php
namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\LiqPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LiqPayController extends Controller
{
    public function callback(Request $request, LiqpayService $liqpayService)
    {
        $validated = $request->validate([
            'data' => ['required', 'string'],
            'signature' => ['required', 'string'],
        ]);

        $data = $validated['data'];
        $signature = $validated['signature'];

        if (!$liqpayService->validateSignature($data, $signature)) {
            return response('Invalid signature', 400);
        }

        try {
            $payload = $liqpayService->decodeData($data);
        }
        catch (\Exception $e) {
            Log::error('LiqPay Error: ' . $e->getMessage());

            return response('Bad request', 400);
        }

        $order_id = (int) $payload['order_id'];
        $order = Order::find($order_id);

        if (!$order) {
            return response('OK', 200);
        }

        if (in_array($order->status, OrderStatus::withoutPending())) {
            return response('OK', 200);
        }

        switch ($payload['status']) {
            case 'success':
                $order->status = OrderStatus::CREATED;
                $order->paid_at = now();
                break;
            case 'failure':
            case 'error':
                $order->status = OrderStatus::CANCELLED;
                break;
            case 'processing':
                $order->status = OrderStatus::PENDING_PAYMENT;
                break;
            default:
                break;
        }

        $order->save();

        return response('OK', 200);
    }
}
