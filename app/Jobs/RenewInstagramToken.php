<?php
namespace App\Jobs;

use App\Models\InstagramAccount;
use App\Services\Instagram\SocialFeedService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class RenewInstagramToken implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public InstagramAccount $instagramAccount,
    ) {}

    /**
     * @throws ConnectionException
     */
    public function handle(SocialFeedService $service): void
    {
        $api_url = $service->getRenewTokenURL(
            $this->instagramAccount->access_token,
            $this->attempts(),
        );

        $response = Http::timeout(10)
            ->connectTimeout(5)
            ->get($api_url);

        if ($response->failed()) {
            throw new ConnectionException('Instagram token renew failed: ' . $response->body());
        }

        $data = $response->json();

        $accessToken = $data['access_token'];
        $expires_in = intval($data['expires_in']);

        $this->instagramAccount->update([
            'token_expires_at' => now()->addSeconds($expires_in),
            'access_token' => $accessToken,
        ]);
    }
}
