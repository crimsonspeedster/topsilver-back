<?php
namespace App\Http\Resources;

use App\Models\NPWarehouse;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin NPWarehouse
 */
class NPWarehouseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name' => $this->name,
            'ref' => $this->ref,
            'type' => $this->type,
            'address' => $this->address,
        ];
    }
}
