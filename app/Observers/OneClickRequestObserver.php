<?php
namespace App\Observers;

use App\Enums\OrderStatus;
use App\Jobs\UpdateOneClickRequestSellingCounts;
use App\Models\OneClickRequest;

class OneClickRequestObserver
{
    public function created(OneClickRequest $order): void
    {
        if ($order->status === OrderStatus::COMPLETED) {
            UpdateOneClickRequestSellingCounts::dispatch($order->id)->onQueue('filters');
        }
    }
}
