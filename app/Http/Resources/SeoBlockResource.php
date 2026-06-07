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
            'content' => $this->blocks,
        ];
    }
}
