<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\WishlistResource;
use App\Models\WishlistItem;
use App\Services\WishlistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    public function __construct(
        protected WishlistService $wishlistService,
    ) {}

    public function show(Request $request)
    {
        $wishlist = $request->attributes->get('wishlist');

        if (!$wishlist) {
            return response()->json([
                'data' => [
                    'items' => [],
                    'items_count' => 0,
                ],
            ]);
        }

        $wishlist->load([
            'items.product.sluggable',
            'items.product.variants',
            'items.product.labels',
            'items.product.categories.sluggable',
            'items.product.collections.sluggable',
            'items.product.promotions.sluggable',
        ]);

        return response()->json([
            'data' => new WishlistResource($wishlist),
        ]);
    }

    public function store(Request $request)
    {
        $wishlistObject = $this->wishlistService->getOrCreateWishlist($request);
        $wishlist = $wishlistObject['wishlist'];
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $wishlist = DB::transaction(function () use ($wishlist, $data) {
            WishlistItem::firstOrCreate([
                'wishlist_id' => $wishlist->id,
                'product_id' => $data['product_id'],
            ]);

            return $wishlist;
        });

        $wishlist->loadMissing([
            'items.product.sluggable',
            'items.product.variants',
            'items.product.labels',
            'items.product.categories.sluggable',
            'items.product.collections.sluggable',
            'items.product.promotions.sluggable',
        ]);

        $response = response()->json([
            'data' => new WishlistResource($wishlist),
        ]);

        if ($wishlistObject['new_token']) {
            $response->cookie(
                'wishlist_token',
                $wishlistObject['new_token'],
                60 * 24 * 3,
            );
        }

        return $response;
    }

    public function destroy(int $product_id, Request $request)
    {
        $wishlist = $request->attributes->get('wishlist');

        abort_if(!$wishlist, 404);

        $wishlist = DB::transaction(function () use ($wishlist, $product_id) {
            $item = WishlistItem::where('wishlist_id', $wishlist->id)
                ->where('product_id', $product_id)
                ->firstOrFail();

            $item->delete();

            return $wishlist;
        });

        $wishlist->loadMissing([
            'items.product.sluggable',
            'items.product.variants',
            'items.product.labels',
            'items.product.categories.sluggable',
            'items.product.collections.sluggable',
            'items.product.promotions.sluggable',
        ]);

        return response()->json([
            'data' => new WishlistResource($wishlist),
        ]);
    }
}
