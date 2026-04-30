<?php
namespace App\Services;

use App\Enums\CouponTypes;
use App\Models\Cart;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function isValid(Coupon $coupon): bool
    {
        if (!$coupon->is_active) {
            return false;
        }

        if ($coupon->starts_at && now()->lt($coupon->starts_at)) {
            return false;
        }

        if ($coupon->expires_at && now()->gt($coupon->expires_at)) {
            return false;
        }

        return true;
    }

    public function hasReachedUsageLimit(Coupon $coupon): bool
    {
        if ($coupon->usage_limit === null) {
            return false;
        }

        return $coupon->used_count >= $coupon->usage_limit;
    }

    public function applyToCart(Cart $cart, Coupon $coupon): Cart
    {
        return DB::transaction(function () use ($cart, $coupon) {
            $cart = Cart::where('id', $cart->id)->lockForUpdate()->first();
            $coupon = Coupon::where('id', $coupon->id)->lockForUpdate()->first();

            if (!$this->isValid($coupon)) {
                throw ValidationException::withMessages([
                    'code' => 'Coupon is not valid.',
                ]);
            }

            if ($this->hasReachedUsageLimit($coupon)) {
                throw ValidationException::withMessages([
                    'code' => 'Coupon usage limit reached.',
                ]);
            }

            if ($cart->coupon_id !== null) {
                throw ValidationException::withMessages([
                    'code' => 'Cart already has a coupon applied.',
                ]);
            }

            if ($cart->subtotal <= 0) {
                throw ValidationException::withMessages([
                    'code' => 'Cart is empty.',
                ]);
            }

            $cart->coupon()->associate($coupon);
            $cart->save();

            return $cart->fresh(['coupon']);
        });
    }

    public function removeFromCart(Cart $cart): Cart
    {
        return DB::transaction(function () use ($cart) {
            $cart = Cart::where('id', $cart->id)
                ->lockForUpdate()
                ->first();

            if (!$cart->coupon_id) {
                throw ValidationException::withMessages([
                    'coupon' => 'No coupon applied.',
                ]);
            }

            $cart->coupon()->dissociate();
            $cart->save();

            return $cart->fresh(['coupon']);
        });

    }

    public function calculateDiscount(Cart $cart, Coupon | null $coupon): float
    {
        if (!$coupon) {
            return 0;
        }

        $subtotal = $cart->subtotal;

        return match ($coupon->type) {
            CouponTypes::PERCENT => round($subtotal * ($coupon->value / 100), 2),
            CouponTypes::FIXED   => min($coupon->value, $subtotal),
            default   => 0,
        };
    }
}
