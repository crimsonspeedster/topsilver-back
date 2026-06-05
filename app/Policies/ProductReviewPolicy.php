<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Enums\ReviewPermissionStatus;
use App\Enums\UserRoles;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ProductReview $review): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
       return true;
    }

    public function update(User $user, ProductReview $review): bool
    {
       return true;
    }

    public function delete(User $user, ProductReview $review): bool
    {
       return true;
    }

    public function createForProduct(User $user, Product $product): Response
    {
        if (in_array($user->role, [UserRoles::Admin, UserRoles::Developer])) {
            return Response::allow();
        }

        if (!$user->email_verified_at) {
            return Response::deny('Підтвердіть email для залишення відгуку.');
        }

        $status = $this->canCustomerReview($user, $product);

        return match ($status) {
            ReviewPermissionStatus::Allowed => Response::allow(),

            ReviewPermissionStatus::NotPurchased =>
            Response::deny('Ви можете залишити відгук лише після покупки товару.'),

            ReviewPermissionStatus::AlreadyReviewed =>
            Response::deny('Ви вже залишали відгук до цього товару.'),
        };
    }

    public function reply(User $user, ProductReview $review): Response
    {
        if (in_array($user->role, [UserRoles::Admin, UserRoles::Developer])) {
            return Response::allow();
        }

        return $review->user_id === $user->id
            ? Response::allow()
            : Response::deny('Ви можете відповідати лише на власні відгуки.');
    }

    private function canCustomerReview(User $user, Product $product): ReviewPermissionStatus
    {
        $hasPurchased = OrderItem::where('entity_id', $product->id)
            ->where('entity_type', Product::class)
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->where('status', '!=', OrderStatus::CANCELLED);
            })
            ->exists();

        if (!$hasPurchased) {
            return ReviewPermissionStatus::NotPurchased;
        }

        $alreadyReviewed = ProductReview::where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->whereNull('parent_id')
            ->exists();

        if ($alreadyReviewed) {
            return ReviewPermissionStatus::AlreadyReviewed;
        }

        return ReviewPermissionStatus::Allowed;
    }
}
