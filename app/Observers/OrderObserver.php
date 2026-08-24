<?php
namespace App\Observers;

use App\Enums\OrderStatus;
use App\Jobs\UpdateOneClickRequestSellingCounts;
use App\Jobs\UpdateOrderSellingCounts;
use App\Models\OneClickRequest;
use App\Models\Order;

class OrderObserver
{
    public function created(Order | OneClickRequest $order): void
    {
        if ($order->status === OrderStatus::COMPLETED) {
            if ($order instanceof OneClickRequest) {
                UpdateOneClickRequestSellingCounts::dispatch($order->id)->onQueue('filters');
            }
            else {
                UpdateOrderSellingCounts::dispatch($order->id)->onQueue('filters');
            }
        }
    }

    public function updated(Order | OneClickRequest $order): void
    {
        if (
            $order->wasChanged('status')
            && $order->status === OrderStatus::COMPLETED
        ) {
            if ($order instanceof OneClickRequest) {
                UpdateOneClickRequestSellingCounts::dispatch($order->id)->onQueue('filters');
            }
            else {
                UpdateOrderSellingCounts::dispatch($order->id)->onQueue('filters');
            }
        }
    }
}
