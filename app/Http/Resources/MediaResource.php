<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @mixin Media
 * */

class MediaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->getUrl(),
        ];
    }
}
