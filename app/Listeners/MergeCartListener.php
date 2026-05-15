<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\CartService;
use Illuminate\Auth\Events\Login;

class MergeCartListener
{
    public function __construct()
    {

    }

    public function handle(Login $event): void
    {
        /** @var User $user */
        $user = $event->user;

        $cartToken = request()->cookie('cart_token')
            ?? request()->header('X-Cart-Token');

        if ($cartToken) {
            app(CartService::class)->mergeGuestCartWithUser(
                $cartToken,
                $user
            );
        }
    }
}
