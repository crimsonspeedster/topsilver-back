<?php
namespace App\Listeners;

use App\Events\UserEmailChanged;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendVerifyNotification implements ShouldQueue
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
    public function handle(UserEmailChanged $event): void
    {
        $event->user->sendEmailVerificationNotification();
    }
}
