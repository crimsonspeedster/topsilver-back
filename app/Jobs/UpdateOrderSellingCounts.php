<?php
namespace App\Jobs;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateOrderSellingCounts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $orderId
    ) {}

    public function handle(): void
    {
        $order = Order::query()
            ->with('items.entity')
            ->find($this->orderId);

        if (!$order) {
            return;
        }

        $productIds = $order->items
            ->map(fn (OrderItem $item) => $item->entity)
            ->filter(fn ($entity) => $entity instanceof Product)
            ->pluck('id')
            ->unique();

        if ($productIds->isEmpty()) {
            return;
        }

        Product::whereIn('id', $productIds)->increment('selling_count');
    }
}
