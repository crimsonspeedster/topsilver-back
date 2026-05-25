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
            'type' => $this->type,
            'link' => $this->link,
            'media' => new MediaResource($this->getFirstMedia('media')),
        ];
    }
}
