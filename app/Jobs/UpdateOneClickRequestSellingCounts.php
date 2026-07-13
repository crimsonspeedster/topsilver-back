<?php
namespace App\Jobs;

use App\Models\OneClickRequest;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateOneClickRequestSellingCounts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $oneClickRequestId,
    ) {}

    public function handle(): void
    {
        $oneClickRequest = OneClickRequest::find($this->oneClickRequestId);

        if (!$oneClickRequest) {
            return;
        }

        $productId = $oneClickRequest->product_id;

        if (!$productId) {
            return;
        }

        Product::where('id', $productId)->increment('selling_count');
    }
}
