<?php
namespace App\Http\Resources;

use App\Models\NPCity;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin NPCity
 */
class NPCityResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'ref' => $this->ref,
            'name' => $this->name,
            'settlement_type' => $this->settlement_type,
        ];
    }
}
