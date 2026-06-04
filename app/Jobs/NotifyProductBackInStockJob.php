<?php
namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductSubscriber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;

class NotifyProductBackInStockJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue;

    public function __construct(
        public int $product_id,
    ) {}

    public function handle(): void
    {
        if (!Product::whereKey($this->product_id)->exists()) {
            return;
        }

        ProductSubscriber::query()
            ->where('product_id', $this->product_id)
            ->chunkById(100, function ($subscribers) {
                foreach ($subscribers as $subscriber) {
                    SendBackInStockEmailJob::dispatch($subscriber->id);
                }
            });
    }
}
