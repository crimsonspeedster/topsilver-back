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
        $user = null;

        if ($token = $request->bearerToken()) {
            $accessToken = PersonalAccessToken::findToken($token);

            if ($accessToken) {
                $user = $accessToken->tokenable;

                Auth::setUser($user);
            }
        }

        $cartToken = $request->cookie('cart_token')
            ?? $request->header('X-Cart-Token');

        $cart = null;
        $isNewToken = false;

        if ($user) {
            $cart = Cart::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'user_id' => $user->id,
                    'last_modified' => now(),
                ]
            );
        }

        if (!$cart) {
            if (!$cartToken) {
                $cartToken = (string) Str::uuid();
                $isNewToken = true;
            }

            $cart = Cart::firstOrCreate(
                ['cart_token' => $cartToken],
                [
                    'last_modified' => now(),
                ]
            );
        }

        $request->attributes->set('cart', $cart);
        $response = $next($request);

        if (!$user) {
            if ($isNewToken) {
                cookie()->queue(
                    'cart_token',
                    $cartToken,
                    60 * 24 * 3,
                    '/',
                    null,
                    false,
                    true,
                    false,
                    'Lax'
                );
            }

            $response->headers->set('X-Cart-Token', $cartToken);
        }

        return $response;
    }
}
