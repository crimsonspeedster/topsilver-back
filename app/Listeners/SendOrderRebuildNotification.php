<?php

namespace App\Listeners;

use App\Events\OrderRebuild;
use App\Mail\OrderRebuildMail;
use App\Services\CurrencyService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderRebuildNotification implements ShouldQueue
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
    public function handle(OrderRebuild $event): void
    {
        $order = $event->order;

        if ($order->email) {
            Mail::to($order->email)
                ->send(new OrderRebuildMail(
                    $order,
                    app(CurrencyService::class)
                ));
        }
    }
}
