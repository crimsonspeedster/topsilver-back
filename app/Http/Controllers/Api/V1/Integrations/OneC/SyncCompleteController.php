<?php
namespace App\Http\Controllers\Api\V1\Integrations\OneC;

use App\Http\Controllers\Controller;
use App\Jobs\RebuildProductFilterIndexJob;
use App\Models\Product;
use Illuminate\Http\Request;

class SyncCompleteController extends Controller
{
    public function update(Request $request)
    {
        Product::query()
            ->select('id')
            ->chunkById(1000, function ($products) {
                foreach ($products as $product) {
                    RebuildProductFilterIndexJob::dispatch($product->id)
                        ->onQueue('filters');
                }
            });

        return response()->json([
            'success' => true,
        ]);
    }
}
