<?php
namespace App\Http\Resources;

use App\Models\IntegrationBatchError;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin IntegrationBatchError
 * */
class IntegrationBatchErrorResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'item_index' => $this->item_index,
            'field' => $this->field,
            'code' => $this->code,
            'message' => $this->message,
        ];
    }
}
