<?php

namespace App\Http\Middleware;

use App\Models\Cart;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class ResolveCart
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        $cartToken = $request->cookie('cart_token')
            ?? $request->header('X-Cart-Token');

        $cart = null;

        if ($user) {
            $cart = Cart::where('user_id', $user->id)->first();
        } elseif ($cartToken) {
            $cart = Cart::where('cart_token', $cartToken)->first();
        }

        $request->attributes->set('cart', $cart);

        return $next($request);
    }
}
