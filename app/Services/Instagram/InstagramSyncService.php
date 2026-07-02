<?php
namespace App\Services\Instagram;

use App\Enums\InstagramPostTypes;
use App\Jobs\DispatchMediaImportBatchJob;
use App\Jobs\SyncInstagramMediaJob;
use App\Models\InstagramPost;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class InstagramSyncService
{
    public function __construct(
        private readonly InstagramApiService $api,
    ) {}

    public function syncPage(string $accountID, string $token, ?string $after = null): void
    {
        $batchItems = [];

        try {
            $response = $this->api->getMedia($accountID, $token, $after);
        }
        catch (ConnectionException|RequestException $e) {
            return;
        }

        foreach ($response['data'] ?? [] as $media) {
            $post = $this->upsertMedia($media);

            if ($post && $post->wasRecentlyCreated) {
                $media_url = $media['media_url'] ?? null;

                if ($media_url) {
                    $batchItems[] = [
                        'id' => $post->id,
                        'collection' => 'media',
                        'urls' => [$media_url],
                    ];
                }
            }
        }

        if (!empty($batchItems)) {
            DispatchMediaImportBatchJob::dispatch(
                InstagramPost::class,
                $batchItems
            )->onQueue('media');
        }

        $nextAfter = $response['paging']['cursors']['after'] ?? null;

        if ($nextAfter) {
            SyncInstagramMediaJob::dispatch(
                $accountID,
                $token,
                $nextAfter
            )
                ->onQueue('import')
                ->delay(now()->addSeconds(2));
        }
    }

    private function upsertMedia(array $media): ?InstagramPost
    {
        if (!isset(
                $media['id'],
                $media['media_type'],
                $media['permalink'],
                $media['timestamp']
            )) {
            return null;
        }

        $type = InstagramPostTypes::tryFrom($media['media_type']);

        if (!$type) {
            return null;
        }

        return InstagramPost::updateOrCreate(
            [
                'instagram_media_id' => $media['id'],
            ],
            [
                'type' => $type,
                'link' => $media['permalink'],
                'published_at' => $media['timestamp'],
            ]
        );
    }
}
