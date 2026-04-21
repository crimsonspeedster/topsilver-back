<?php

namespace App\Observers;

use App\Jobs\RebuildProductFilterIndexJob;
use App\Models\Category;

class CategoryObserver
{
    /**
     * Handle the Category "created" event.
     */
    public function created(Category $category): void
    {
        //
    }

    public function saved(Category $category): void
    {
        $productIds = $category->products()->pluck('products.id');

        foreach ($productIds as $productId) {
            RebuildProductFilterIndexJob::dispatch($productId)
                ->onQueue('filters')
                ->afterCommit();
        }
    }

    /**
     * Handle the Category "updated" event.
     */
    public function updated(Category $category): void
    {
        //
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        //
    }

    /**
     * Handle the Category "restored" event.
     */
    public function restored(Category $category): void
    {
        //
    }

    /**
     * Handle the Category "force deleted" event.
     */
    public function forceDeleted(Category $category): void
    {
        //
    }
}
