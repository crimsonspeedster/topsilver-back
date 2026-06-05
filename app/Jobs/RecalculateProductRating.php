<?php
namespace App\Jobs;

use App\Enums\ReviewStatus;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class RecalculateProductRating implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $productId
    ) {}

    public function handle(): void
    {
        $product = Product::find($this->productId);

        if (!$product) {
            return;
        }

        $stats = DB::table('product_reviews')
            ->where('product_id', $this->productId)
            ->where('status', ReviewStatus::APPROVED)
            ->whereNull('parent_id')
            ->whereNotNull('rating')
            ->selectRaw('
                COUNT(*) as rating_count,
                AVG(rating) as rating_avg,

                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as r5,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as r4,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as r3,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as r2,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as r1
            ')
            ->first();

        $product->update([
            'rating_count' => (int) $stats->rating_count,
            'rating_avg' => round($stats->rating_avg ?? 0, 2),
            'rating_distribution' => [
                5 => (int) $stats->r5,
                4 => (int) $stats->r4,
                3 => (int) $stats->r3,
                2 => (int) $stats->r2,
                1 => (int) $stats->r1,
            ],
        ]);
    }
}
