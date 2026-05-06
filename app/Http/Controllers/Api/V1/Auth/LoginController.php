<?php
namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Cart;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function __invoke (Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('site_token', [], now()->addDays(7))->plainTextToken;

        $cartToken = $request->cookie('cart_token')
            ?? $request->header('X-Cart-Token');

        if ($cartToken) {
            app(CartService::class)->mergeGuestCartWithUser($cartToken, $user);
        }

        return response()->json([
            'data' => new UserResource(
                $user->load('profile.city.region')
            ),
        ])
            ->cookie(
                'access_token',
                $token,
                60 * 24 * 7,
                '/',
                null,
                true,
                true,
                false,
                'Strict'
            )
            ->cookie(cookie()->forget('cart_token'));
    }
}
