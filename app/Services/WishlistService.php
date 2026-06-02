<?php
namespace App\Services;

use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WishlistService
{
    public function getOrCreateWishlist(Request $request): array
    {
        $user = Auth::user();

        if ($user) {
            return [
                'wishlist' => Wishlist::firstOrCreate([
                    'user_id' => $user->id,
                ]),
                'new_token' => null,
            ];
        }

        $token = $request->cookie('wishlist_token')
            ?? $request->header('X-Wishlist-Token');
        $isNew = false;

        if (!$token) {
            $token = (string) Str::uuid();
            $isNew = true;
        }

        $wishlist = Wishlist::firstOrCreate([
            'wishlist_token' => $token,
        ]);

        return [
            'wishlist' => $wishlist,
            'new_token' => $isNew ? $token : null,
        ];
    }

    public function mergerGuestWishlistWithUser(string $wishlistToken, User $user): void
    {
        $guestWishlist = Wishlist::where('wishlist_token', $wishlistToken)
            ->with('items')
            ->first();

        if (!$guestWishlist || $guestWishlist->items->isEmpty()) {
            return;
        }

        $userWishlist = Wishlist::firstOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'last_modified' => now(),
            ]
        );

        DB::transaction(function () use ($guestWishlist, $userWishlist) {
            $this->mergerWishlist($guestWishlist, $userWishlist);
        });
    }

    public function mergerWishlist(Wishlist $guestWishlist, Wishlist $userWishlist): Wishlist
    {
        $guestItems = $guestWishlist->items()
            ->select('product_id')
            ->get()
            ->pluck('product_id')
            ->toArray();

        $existingItems = $userWishlist->items()
            ->whereIn('product_id', $guestItems)
            ->pluck('product_id')
            ->toArray();

        $itemsToInsert = array_diff($guestItems, $existingItems);

        if (!empty($itemsToInsert)) {
            $insertData = array_map(fn ($productId) => [
                'wishlist_id' => $userWishlist->id,
                'product_id' => $productId,
                'created_at' => now(),
                'updated_at' => now(),
            ], $itemsToInsert);

            WishlistItem::insert($insertData);
        }

        $this->removeGuestWishlist($guestWishlist);

        return $userWishlist;
    }

    private function removeGuestWishlist(Wishlist $wishlist): void
    {
        $wishlist->items()->delete();
        $wishlist->delete();
    }
}
