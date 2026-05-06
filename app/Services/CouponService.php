<?php
namespace App\Services;

use App\Models\Coupon;
use Exception;

class CouponService
{
    /**
     * @throws Exception
     */
    public function validate(Coupon $coupon): void
    {
        if (!$coupon->is_active) {
            throw new Exception('Coupon is not active');
        }

        if ($coupon->starts_at && now()->lt($coupon->starts_at)) {
            throw new Exception('Coupon is not started yet');
        }

        if ($coupon->expires_at && now()->gt($coupon->expires_at)) {
            throw new Exception('Coupon has expired');
        }

        if ($this->hasReachedUsageLimit($coupon)) {
            throw new Exception('Coupon usage limit reached');
        }
    }

    public function hasReachedUsageLimit(Coupon $coupon): bool
    {
        if ($coupon->usage_limit === null) {
            return false;
        }

        return $coupon->used_count >= $coupon->usage_limit;
    }
}
