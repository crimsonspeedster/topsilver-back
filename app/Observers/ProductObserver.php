<?php

namespace App\Observers;

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
        //
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
