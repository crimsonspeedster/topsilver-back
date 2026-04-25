<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class NovaPoshtaService
{
    protected string $url = 'https://api.novaposhta.ua/v2.0/json/';

    public function call(string $model, string $method, array $properties = []): array
    {
        $payload = [
            'apiKey' => config('services.nova_poshta_key'),
            'modelName' => $model,
            'calledMethod' => $method,
        ];

        if (!empty($properties)) {
            $payload['methodProperties'] = $properties;
        }

        $response = Http::post($this->url, $payload);

        logger()->info('RESPONSE -> ', $response->json());

        return $response->json('data') ?? [];
    }

    public function getAreas(): array
    {
        return $this->call('AddressGeneral', 'getAreas');
    }

    public function getCities(int $page = 1, int $limit = 500): array
    {
        return $this->call('AddressGeneral', 'getCities', [
            'Page' => (string) $page,
            'Limit' => (string) $limit,
        ]);
    }

    public function getWarehouses(int $page = 1, int $limit = 500): array
    {
        return $this->call('AddressGeneral', 'getWarehouses', [
            'Page' => (string) $page,
            'Limit' => (string) $limit,
        ]);
    }
}
