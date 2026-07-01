<?php
namespace App\Http\Resources;

use App\Models\IntegrationBatch;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin IntegrationBatch
 * */
class IntegrationBatchResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'entity' => $this->entity,
            'status' => $this->status,
            'items_count' => $this->items_count,
            'processed_count' => $this->processed_count,
            'failed_count' => $this->failed_count,
            'error_message' => $this->error_message,
            'started_at' => $this->started_at,
            'finished_at' => $this->finished_at,
            'errors' => IntegrationBatchErrorResource::collection($this->whenLoaded('errors')),
        ];
    }
}
