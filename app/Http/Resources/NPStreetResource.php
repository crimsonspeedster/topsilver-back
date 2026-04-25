<?php
namespace App\Http\Resources;

use App\Models\NPStreet;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin NPStreet
 */
class NPStreetResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'ref' => $this->ref,
            'name' => $this->name,
            'type' => $this->type,
        ];
    }
}
