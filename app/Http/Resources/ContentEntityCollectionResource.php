<?php
namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Post
 */
class ContentEntityCollectionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'media' => new MediaResource($this->getFirstMedia('media')),
        ];
    }
}
