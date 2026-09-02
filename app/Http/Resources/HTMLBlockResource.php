<?php
namespace App\Http\Resources;

use App\Models\HTMLBlock;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin HTMLBlock
 */

class HTMLBlockResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'blocks' => $this->blocks,
        ];
    }
}
