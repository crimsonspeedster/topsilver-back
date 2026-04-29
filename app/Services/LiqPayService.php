<?php
namespace App\Services;

use App\Models\Order;
use Exception;
use LiqPay;

class LiqPayService
{
    public function __construct(
        protected string $public_key = '',
        protected string $private_key = '',
    ) {
        $this->public_key = config('services.liqpay.public_key');
        $this->private_key = config('services.liqpay.private_key');
    }

    public function generatePaymentForm(Order $order): array
    {
        $liqpay = new LiqPay(
            $this->public_key,
            $this->private_key,
        );

        return $liqpay->cnb_form_raw([
            'action' => 'pay',
            'version' => '3',
            'amount' => $order->total,
            'currency' => 'UAH',
            'description' => "Order #{$order->id}",
            'order_id' => (string)$order->id,
            'result_url' => config('app.frontend_url') . '/payment/result?order_id=' . $order->id,
            'server_url' => route('payments.liqpay.callback'),
        ]);
    }

    /**
     * @throws Exception
     */
    public function decodeData(string $data): array
    {
        $json = base64_decode($data);

        if ($json === false) {
            throw new Exception('Invalid base64 data');
        }

        $decoded = json_decode($json, true);

        if (!is_array($decoded)) {
            throw new Exception('Invalid JSON data');
        }

        return $decoded;
    }

    public function validateSignature(string $data, string $signature): bool
    {
        $expectedSignature = base64_encode(
            sha1($this->private_key . $data . $this->private_key, true)
        );

        return hash_equals($expectedSignature, $signature);
    }
}
