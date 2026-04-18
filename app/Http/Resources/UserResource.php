<?php
namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'email' => $this->email,
            'phone' => $this->phone,
            'profile' => new ProfileResource($this->whenLoaded('profile')),
        ];
    }
}
