<?php
namespace App\Http\Resources;

use App\Models\Seo;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Seo
 * */

class SeoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'keywords' => $this->keywords,
        ];
    }
}
