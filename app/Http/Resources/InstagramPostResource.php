<?php
namespace App\Http\Resources;

use App\Models\InstagramPost;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * @mixin InstagramPost
 */

class InstagramPostResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'link' => $this->link,
            'type' => $this->type,
            'caption' => $this->caption,
            'media' => new MediaResource($this->getFirstMedia('media')),
            'videos' => new MediaResource($this->getFirstMedia('videos')),
        ];
    }
}
