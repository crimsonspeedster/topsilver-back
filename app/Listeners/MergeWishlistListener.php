<?php
namespace App\Listeners;

use App\Events\UserLoggedIn;
use App\Services\WishlistService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class MergeWishlistListener implements ShouldQueue
{
    public function __construct()
    {

    }

    public function handle(UserLoggedIn $event): void
    {
        $user = $event->user;
        $wishlistToken = $event->wishlistToken;

        if ($wishlistToken) {
            app(WishlistService::class)->mergerGuestWishlistWithUser(
                $wishlistToken,
                $user
            );
        }
    }
}
