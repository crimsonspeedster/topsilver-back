<?php
namespace App\Http\Middleware;

use App\Models\Cart;
use App\Models\Wishlist;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ResolveWishlist
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        $wishlistToken = $request->cookie('wishlist_token')
            ?? $request->header('X-Wishlist-Token');

        $wishlist = null;

        if ($user) {
            $wishlist = Wishlist::where('user_id', $user->id)->first();
        } elseif ($wishlistToken) {
            $wishlist = Wishlist::where('wishlist_token', $wishlistToken)->first();
        }

        $request->attributes->set('wishlist', $wishlist);

        return $next($request);
    }
}
