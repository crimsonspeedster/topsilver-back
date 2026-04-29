<?php
namespace App\Services;

use App\Models\Order;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;


class MonobankPay
{
    protected string $baseUrl = 'https://api.monobank.ua/api/merchant';

    public function __construct(
        protected string $monobank_token = '',
    )
    {
        $this->monobank_token = config("services.monobank_token");
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createInvoice(Order $order): array
    {
        $payload = $this->buildPayload($order);

        return Http::withHeaders([
            'X-Token' => $this->monobank_token,
        ])
            ->post($this->baseUrl . '/invoice/create', $payload)
            ->throw()
            ->json();
    }

    protected function buildPayload(Order $order): array
    {
        return [
            'amount' => (int) round($order->total * 100), // копейки
            'ccy' => 980,
            'merchantPaymInfo' => [
                'reference' => (string) $order->id,
                'destination' => "Order #{$order->id}",
                'comment' => "Order #{$order->id}",
                'basketOrder' => $this->buildBasket($order),
            ],
            'redirectUrl' => config('app.frontend_url') . "/payment/result?order_id={$order->id}",
            'successUrl' => config('app.frontend_url') . "/payment/success?order_id={$order->id}",
            'failUrl' => config('app.frontend_url') . "/payment/fail?order_id={$order->id}",
            'webHookUrl' => route('payments.monobank.callback'),
            'validity' => 3600,
        ];
    }

    protected function buildBasket(Order $order): array
    {
        return $order->items->map(function ($item) {
            return [
                'name' => $item->product_name,
                'qty' => (int) $item->quantity,
                'sum' => (int) round($item->product_price * 100),
                'total' => (int) round($item->total * 100),
                'unit' => 'шт.',
                'icon' => $item->product_image,
                'code' => (string) $item->product_id,
            ];
        })->toArray();
    }

    public function getPublicKey(): string
    {
        return Cache::rememberForever('monobank_public_key', function () {
            return $this->fetchPublicKey();
        });
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function refreshPublicKey(): string
    {
        $key = $this->fetchPublicKey();

        Cache::put('monobank_public_key', $key, 60 * 60 * 24);

        return $key;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    protected function fetchPublicKey(): string
    {
        return Http::withHeaders([
            'X-Token' => $this->monobank_token,
        ])
            ->get($this->baseUrl . '/pubkey')
            ->throw()
            ->body();
    }

    protected function verifyWithKey(string $payload, string $xSign, string $publicKeyBase64): bool
    {
        $signature = base64_decode($xSign);
        $publicKey = openssl_get_publickey(base64_decode($publicKeyBase64));

        if (!$publicKey) {
            return false;
        }

        return openssl_verify(
                $payload,
                $signature,
                $publicKey,
                OPENSSL_ALGO_SHA256
            ) === 1;
    }

    public function verifyWebhookSignature(string $payload, string $xSign): bool
    {
        if ($this->verifyWithKey($payload, $xSign, $this->getPublicKey())) {
            return true;
        }

        try {
            $newKey = $this->refreshPublicKey();
        } catch (Throwable $e) {
            Log::error('Monobank key refresh failed: ' . $e->getMessage());

            return false;
        }

        return $this->verifyWithKey($payload, $xSign, $newKey);
    }

    /**
     * @throws Exception
     */
    public function decodePayload(string $payload): array
    {
        $data = json_decode($payload, true);

        if (!is_array($data)) {
            throw new Exception('Invalid JSON data');
        }

        return $data;
    }
}
