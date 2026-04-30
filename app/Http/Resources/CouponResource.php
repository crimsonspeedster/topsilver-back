<?php
namespace App\Http\Resources;

use App\Models\Coupon;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Coupon
 */
class CouponResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'code' => $this->code,
            'type' => $this->type,
            'value' => $this->value,
        ];
    }
}
