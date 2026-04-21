<?php

namespace App\Jobs;

use App\Models\Product;
use App\Services\ProductFilterIndexService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;

class RebuildProductFilterIndexJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable, Dispatchable, InteractsWithQueue;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $productId
    ) {}

    public function uniqueId(): string
    {
        return (string) $this->productId;
    }

    public function uniqueFor(): int
    {
        return 60;
    }

    /**
     * Execute the job.
     */
    public function handle(ProductFilterIndexService $service): void
    {
        $product = Product::with([
            'variants.attributeTerms',
            'attributeTerms',
            'categories',
            'collections',
        ])->find($this->productId);

        if (!$product) {
            return;
        }

        $service->rebuild($product);
    }
}
