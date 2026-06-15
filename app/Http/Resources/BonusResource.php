<?php
namespace App\Http\Resources;

use App\Models\Bonus;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @mixin Bonus
 */

class BonusResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'amount' => $this->amount,
            'accrual_from' => $this->accrual_from->format('Y-m-d'),
            'available_from' => $this->available_from->format('Y-m-d'),
            'expires_at' => $this->expires_at->format('Y-m-d'),
        ];
    }
}
