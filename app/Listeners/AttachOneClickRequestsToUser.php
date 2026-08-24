<?php

namespace App\Listeners;

use App\Events\UserLoggedIn;
use App\Events\UserRegistered;
use App\Models\OneClickRequest;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AttachOneClickRequestsToUser implements ShouldQueue
{
    public int $tries = 3;

    public int $timeout = 15;

    public string $queue = 'high';

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
    public function handle(UserLoggedIn $event): void
    {
        $user = $event->user;

        OneClickRequest::query()
            ->whereNull('user_id')
            ->where('phone', $user->phone)
            ->update([
                'user_id' => $user->id,
                'updated_at' => now(),
            ]);
    }
}
