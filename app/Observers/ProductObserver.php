<?php

namespace App\Observers;

use App\Enums\StockStatus;
use App\Jobs\NotifyProductBackInStockJob;
use App\Jobs\RebuildProductFilterIndexJob;
use App\Models\Product;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "saved" event.
     */
    public function saved(Product $product): void
    {
        RebuildProductFilterIndexJob::dispatch($product->id)
            ->onQueue('filters')
            ->afterCommit();
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        if (
            $product->wasChanged('stock_status')
            && $product->getOriginal('stock_status') === StockStatus::OutOfStock
            && $product->stock_status === StockStatus::InStock
        ) {
            NotifyProductBackInStockJob::dispatch($product->id);
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        RebuildProductFilterIndexJob::dispatch($product->id)
            ->onQueue('filters')
            ->afterCommit();
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
