<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AttachOrdersToUser implements ShouldQueue
{
    public int $tries = 3;

    public int $timeout = 10;

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
    public function handle(UserRegistered $event): void
    {
        $user = $event->user;

        Order::query()
            ->whereNull('user_id')
            ->where('phone', $user->phone)
            ->update([
                'user_id' => $user->id,
                'updated_at' => now(),
            ]);
    }
}
