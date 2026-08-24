<?php

namespace App\Listeners;

use App\Events\OneClickOrderCreated;
use App\Mail\OrderInOneClickMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOneClickOrderNotification implements ShouldQueue
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
    public function handle(OneClickOrderCreated $event): void
    {
        $one_click_order = $event->oneClickOrder;

        if ($one_click_order->email) {
            Mail::to($one_click_order->email)
                ->send(new OrderInOneClickMail(
                    $one_click_order
                ));
        }
    }
}
