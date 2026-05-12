<?php
namespace App\Http\Resources;

use App\Models\Video;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Video
 */

class VideoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'thumbnail' => new MediaResource($this->getFirstMedia('thumbnail')),
            'link' => $this->link,
        ];
    }
}
