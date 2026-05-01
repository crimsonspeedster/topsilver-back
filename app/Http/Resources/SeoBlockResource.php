<?php
namespace App\Http\Resources;

use App\Models\SeoBlock;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SeoBlock
 * */

class SeoBlockResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'title' => $this->title,
            'excerpt' => $this->excerpt_resolve,
            'content' => $this->content,
        ];
    }
}
