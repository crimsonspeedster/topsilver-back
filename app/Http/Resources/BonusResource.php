<?php
namespace App\Http\Resources;

use App\Models\Bonus;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property string $amount
 * @property Carbon $accrual_from
 * @property Carbon $available_from
 * @property Carbon $expires_at
 * @mixin Bonus
 */

class BonusResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'amount' => $this->amount,
            'accrual_from' => $this->accrual_from,
            'available_from' => $this->available_from,
            'expires_at' => $this->expires_at,
        ];
    }
}
