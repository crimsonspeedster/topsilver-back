<?php
namespace App\Listeners;

use App\Events\UserLoggedIn;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Contracts\Queue\ShouldQueue;


class MergeCartListener implements ShouldQueue
{
    public int $tries = 3;

    public int $timeout = 15;

    public string $queue = 'high';

    public function __construct()
    {

    }

    public function handle(UserLoggedIn $event): void
    {
        $user = $event->user;
        $cartToken = $event->cartToken;

        if ($cartToken) {
            app(CartService::class)->mergeGuestCartWithUser(
                $cartToken,
                $user
            );
        }
    }
}
