<?php
namespace App\Http\Resources;

use App\Models\Label;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Label
 * */

class LabelResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name' => $this->name,
            'background_color' => $this->background_color,
            'text_color' => $this->text_color,
        ];
    }
}
