<?php
namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Post
 */
class ContentEntityResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'content' => $this->content,
            'seo' => new SeoResource($this->whenLoaded('seo')),
            'seo_block' => new SeoBlockResource($this->whenLoaded('seoBlock')),
            'media' => new MediaResource($this->getFirstMedia('media')),
        ];
    }
}
