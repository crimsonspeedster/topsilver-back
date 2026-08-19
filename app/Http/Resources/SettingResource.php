<?php
namespace App\Http\Resources;

use App\Models\Setting;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Setting
 */
class SettingResource extends JsonResource
{
    public function toArray($request): array
    {
        $value = $this->value;

        if ($this->type === 'image' && !empty($value['data'])) {
            $value['data'] = Storage::disk('public')->url($value['data']);
        }

        return [
            'key' => $this->key,
            'value' => $value,
            'type' => $this->type,
        ];
    }
}
