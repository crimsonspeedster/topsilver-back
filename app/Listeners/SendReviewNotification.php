<?php

namespace App\Listeners;

use App\Enums\ReviewStatus;
use App\Enums\UserRoles;
use App\Events\ReviewCreated;
use App\Mail\ReviewAdminReminderMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendReviewNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ReviewCreated $event): void
    {
        $review = $event->productReview;

        if ($review->status !== ReviewStatus::PENDING)
            return;

        $managers = User::where('role', '=', UserRoles::ShopManager)->get();

        foreach ($managers as $manager) {
            Mail::to($manager->email)->send(new ReviewAdminReminderMail($review));
        }
    }
}
