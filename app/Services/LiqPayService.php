<?php
namespace App\Services;

use App\Models\Order;
use App\Models\PaymentMethod;
use Exception;
use LiqPay;

class LiqPayService
{
    protected string $public_key = '';
    protected string $private_key = '';

    public function __construct(
        protected PaymentMethod $paymentMethod,
    ) {
        $this->public_key = $this->paymentMethod->config['public_key'] ?? '';
        $this->private_key = $this->paymentMethod->config['private_key'] ?? '';
    }

    /**
     * @throws Exception
     */
    public function generatePaymentForm(Order $order): array
    {
        if (!$this->public_key || !$this->private_key) {
            throw new Exception('No public key or private key');
        }

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

    /**
     * @throws Exception
     */
    public function validateSignature(string $data, string $signature): bool
    {
        if (!$this->public_key || !$this->private_key) {
            throw new Exception('No public key or private key');
        }

        $expectedSignature = base64_encode(
            sha1($this->private_key . $data . $this->private_key, true)
        );

        return hash_equals($expectedSignature, $signature);
    }
}
