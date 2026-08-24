<?php
namespace App\Observers;

use App\Enums\ReviewStatus;
use App\Jobs\RecalculateProductRating;
use App\Models\ProductReview;

class ProductReviewObserver
{
    public function created(ProductReview $review): void
    {
        if ($review->status === ReviewStatus::APPROVED) {
            RecalculateProductRating::dispatch($review->product_id)->onQueue('filters');
        }
    }

    public function updated(ProductReview $review): void
    {
        if (
            $review->wasChanged('status')
            && $review->status === ReviewStatus::APPROVED
        ) {
            RecalculateProductRating::dispatch($review->product_id)->onQueue('filters');
        }
    }
}
