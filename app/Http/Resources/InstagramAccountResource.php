<?php
namespace App\Http\Resources;

use App\Models\InstagramAccount;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin InstagramAccount
 */

class InstagramAccountResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'instagram_id' => $this->instagram_id,
            'username' => $this->username,
            'access_token' => $this->access_token,
            'token_expires_at' => $this->token_expires_at,
            'created_at' => $this->created_at,
        ];
    }
}
