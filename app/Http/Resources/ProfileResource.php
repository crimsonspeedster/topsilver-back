<?php
namespace App\Http\Resources;

use App\Enums\SexTypes;
use App\Models\Profile;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property string $name
 * @property string $surname
 * @property string|null $middle_name
 * @property string|null $about
 * @property SexTypes|null $sex
 * @property Carbon|null $dob
 * @property int|null $city_id
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
