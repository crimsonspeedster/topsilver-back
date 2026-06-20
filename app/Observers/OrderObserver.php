<?php
namespace App\Observers;

use App\Enums\OrderStatus;
use App\Jobs\UpdateOrderSellingCounts;
use App\Models\Order;

class OrderObserver
{
    public function created(Order $order): void
    {
        if ($order->status === OrderStatus::COMPLETED) {
            UpdateOrderSellingCounts::dispatch($order->id)->onQueue('high');
        }
    }

    public function updated(Order $order): void
    {
        if (
            $order->wasChanged('status')
            && $order->status === OrderStatus::COMPLETED
        ) {
            UpdateOrderSellingCounts::dispatch($order->id)->onQueue('high');
        }
    }
}
