<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Mail\OrderStatusChangedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusNotification implements ShouldQueue
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
    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;

        if ($order->email) {
            Mail::to($order->email)
                ->send(new OrderStatusChangedMail(
                    $order
                ));
        }
    }
}
