<?php
namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MonobankPay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class MonopayController extends Controller
{
    public function callback(Request $request, MonobankPay $monobankPay)
    {
        $payload = $request->getContent();
        $xSign = $request->header('x-sign');

        if (!$xSign) {
            return response('Missing signature', 400);
        }

        if (!$monobankPay->verifyWebhookSignature($payload, $xSign)) {
            Log::warning('Monobank invalid signature', [
                'payload' => $payload,
            ]);

            return response('Invalid signature', 400);
        }

        try {
            $data = $monobankPay->decodePayload($payload);
        } catch (Throwable $e) {
            Log::error('Monobank decode error: ' . $e->getMessage());

            return response('Bad request', 400);
        }

        $order_id = (int) $data['reference'];
        $order = Order::find($order_id);

        if (!$order) {
            return response('OK', 200);
        }

        if (in_array($order->status, OrderStatus::withoutPending())) {
            return response('OK', 200);
        }

        switch ($data['status']) {
            case 'success':
                $order->status = OrderStatus::CREATED;
                break;
            case 'processing':
                $order->status = OrderStatus::PENDING_PAYMENT;
                break;
            case 'failure':
            case 'expired':
                $order->status = OrderStatus::CANCELLED;
                break;
            default:
                break;
        }

        $order->save();

        return response('OK', 200);
    }
}
