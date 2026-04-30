<?php
namespace App\Http\Resources;

use App\Models\Page;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Page
 */
class PageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'seo' => new SeoResource($this->whenLoaded('seo')),
        ];
    }
}
