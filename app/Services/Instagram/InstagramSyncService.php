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

            if (!$post?->wasRecentlyCreated) {
                continue;
            }

            if ($post->type === InstagramPostTypes::VIDEO) {
                if ($post->media_url) {
                    $batchItems[] = [
                        'id' => $post->id,
                        'collection' => 'videos',
                        'urls' => [$post->media_url],
                    ];
                }

                if ($post->thumbnail_url) {
                    $batchItems[] = [
                        'id' => $post->id,
                        'collection' => 'media',
                        'urls' => [$post->thumbnail_url],
                    ];
                }

                continue;
            }

            if ($post->media_url) {
                $batchItems[] = [
                    'id' => $post->id,
                    'collection' => 'media',
                    'urls' => [$post->media_url],
                ];
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

        if ($media['media_type'] === 'CAROUSEL_ALBUM' && !empty($media['children']['data']) && !empty($media['children']['data'][0])) {
            $media_child = $media['children']['data'][0];
            $media_child['timestamp'] = $media['timestamp'];
            $media_child['caption'] = $media['caption'];
            $media = $media_child;
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
                'caption' => $media['caption'] ?? null,
                'published_at' => $media['timestamp'],
                'thumbnail_url' => $type === InstagramPostTypes::VIDEO ? $media['thumbnail_url'] :  null,
                'media_url' => $media['media_url'] ?? null,
            ]
        );
    }
}
