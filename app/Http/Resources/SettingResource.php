<?php
namespace App\Http\Resources;

use App\Models\Setting;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Setting
 */
class SettingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
            'type' => $this->type,
        ];
    }
}
