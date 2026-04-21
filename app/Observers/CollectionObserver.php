<?php

namespace App\Observers;

use App\Jobs\RebuildProductFilterIndexJob;
use App\Models\Collection;

class CollectionObserver
{
    /**
     * Handle the Collection "created" event.
     */
    public function created(Collection $collection): void
    {
        //
    }

    public function saved(Collection $collection): void
    {
        $productIds = $collection->products()->pluck('products.id');

        foreach ($productIds as $productId) {
            RebuildProductFilterIndexJob::dispatch($productId)
                ->onQueue('filters')
                ->afterCommit();
        }
    }

    /**
     * Handle the Collection "updated" event.
     */
    public function updated(Collection $collection): void
    {
        //
    }

    /**
     * Handle the Collection "deleted" event.
     */
    public function deleted(Collection $collection): void
    {
        //
    }

    /**
     * Handle the Collection "restored" event.
     */
    public function restored(Collection $collection): void
    {
        //
    }

    /**
     * Handle the Collection "force deleted" event.
     */
    public function forceDeleted(Collection $collection): void
    {
        //
    }
}
