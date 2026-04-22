<?php
namespace App\Services;

use App\Enums\ReviewStatus;
use App\Enums\UserRoles;
use App\Models\User;
use Illuminate\Support\Collection;

class ProductReviewService
{
    public function buildTree($grouped, $parentId = null): Collection
    {
        return collect($grouped[$parentId] ?? [])
            ->map(function ($review) use ($grouped) {
                $review->replies = $this->buildTree($grouped, $review->id);

                return $review;
            })
            ->values();
    }

    public function resolveStatus(User $user): ReviewStatus
    {
        if (in_array($user->role, [UserRoles::Admin, UserRoles::Developer])) {
            return ReviewStatus::APPROVED;
        }

        return ReviewStatus::PENDING;
    }
}
