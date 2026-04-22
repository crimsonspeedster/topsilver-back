<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Enums\UserRoles;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;

class ProductReviewPolicy
{
    public function create(User $user, Product $product): bool
    {
        if (in_array($user->role, [UserRoles::Admin, UserRoles::Developer])) {
            return true;
        }

        $hasPurchased = OrderItem::where('product_id', $product->id)
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->where('status', '!=', OrderStatus::CANCELLED);
            })
            ->exists();

        if (!$hasPurchased) {
            return false;
        }

        $alreadyReviewed = ProductReview::where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->whereNull('parent_id')
            ->exists();

        return !$alreadyReviewed;
    }

    public function reply(User $user, ProductReview $review): bool
    {
        return $review->user_id === $user->id;
    }
}
