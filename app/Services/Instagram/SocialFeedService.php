<?php
namespace App\Services\Instagram;

class SocialFeedService
{
    private string $baseUrl = 'https://socialfeed.quadlayers.com';

    public function getOauthURLParams(string $client_id, string $callbackURL): string
    {
        return http_build_query([
            'client_id' => $client_id,
            'redirect_uri' => $this->baseUrl . '/instagram.php',
            'response_type' => 'code',
            'scope' => 'instagram_business_basic',
            'state' => $callbackURL,
        ]);
    }

    public function getRenewTokenURL(string $oldAccessToken, int $attempt = 1): string
    {
        $query = http_build_query([
            'access_token' => $oldAccessToken,
            'renew_count' => $attempt,
        ]);

        return $this->baseUrl . '/renew_instagram.php?' . $query;
    }
}
