<?php
namespace App\Http\Resources;

use App\Models\Profile;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Profile
 */

class ProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name' => $this->name,
            'surname' => $this->surname,
            'middle_name' => $this->middle_name,
            'about' => $this->about,
            'sex' => $this->sex,
            'dob' => $this->dob,
            'city' => new CityResource($this->whenLoaded('city')),
        ];
    }
}
