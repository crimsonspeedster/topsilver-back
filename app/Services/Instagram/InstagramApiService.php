<?php
namespace App\Services\Instagram;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class InstagramApiService
{
    private string $baseUrl = 'https://graph.instagram.com/v25.0';

    /**
     * @throws ConnectionException
     * @throws RequestException
     */
    public function getMedia(string $accountID, string $token, ?string $after = null): array
    {
        $query = [
            'fields' => 'id,alt_text,caption,media_type,media_url,permalink,thumbnail_url,timestamp',
            'access_token' => $token,
            'limit' => 50,
        ];

        if ($after) {
            $query['after'] = $after;
        }

        $response = Http::get("{$this->baseUrl}/{$accountID}/media", $query);

        return $response->throw()->json();
    }
}
