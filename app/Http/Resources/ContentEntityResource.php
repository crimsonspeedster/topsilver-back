<?php
namespace App\Http\Resources;

use App\Http\Resources\Product\ProductCardResource;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Page
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
            'seo_block' => new SeoBlockResource($this->whenLoaded('seoBlock')),
            'media' => new MediaResource($this->getFirstMedia('media')),
            'banner' => new MediaResource($this->getFirstMedia('banner')),
        ];
    }
}
