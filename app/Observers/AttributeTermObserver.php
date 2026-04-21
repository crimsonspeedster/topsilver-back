<?php

namespace App\Observers;

use App\Jobs\RebuildProductFilterIndexJob;
use App\Models\AttributeTerm;

class AttributeTermObserver
{
    /**
     * Handle the AttributeTerm "created" event.
     */
    public function created(AttributeTerm $attributeTerm): void
    {
        //
    }

    /**
     * Handle the Product "saved" event.
     */
    public function saved(AttributeTerm $term): void
    {
        $productIds = $term->products()->pluck('products.id');

        foreach ($productIds as $productId) {
            RebuildProductFilterIndexJob::dispatch($productId)
                ->onQueue('filters')
                ->afterCommit();
        }
    }

    /**
     * Handle the AttributeTerm "updated" event.
     */
    public function updated(AttributeTerm $attributeTerm): void
    {
        //
    }

    /**
     * Handle the AttributeTerm "deleted" event.
     */
    public function deleted(AttributeTerm $attributeTerm): void
    {
        //
    }

    /**
     * Handle the AttributeTerm "restored" event.
     */
    public function restored(AttributeTerm $attributeTerm): void
    {
        //
    }

    /**
     * Handle the AttributeTerm "force deleted" event.
     */
    public function forceDeleted(AttributeTerm $attributeTerm): void
    {
        //
    }
}
