<?php
namespace App\Http\Resources;

use App\Models\NPArea;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin NPArea
 */
class NPAreaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'ref' => $this->ref,
            'name' => $this->name,
        ];
    }
}
