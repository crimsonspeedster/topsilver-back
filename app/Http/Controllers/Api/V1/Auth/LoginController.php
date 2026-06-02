<?php
namespace App\Http\Controllers\Api\V1\Auth;

use App\Events\UserLoggedIn;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Cart;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function __invoke (Request $request)
    {
        $validated = $request->validate([
            'login' => 'required|string',
            'password' => 'required'
        ]);

        $login = $validated['login'];
        $password = $validated['password'];
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $user = User::where($field, $login)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['message' => 'Невірний логін або пароль'], 422);
        }

        Auth::login($user);
        $request->session()->regenerate();

        event(new UserLoggedIn(
            $user,
            $request->cookie('wishlist_token') ?? $request->header('X-Wishlist-Token'),
            $request->cookie('cart_token') ?? $request->header('X-Cart-Token')
        ));

        return response()->json([
            'data' => new UserResource(
                $user->load('profile.city.region')
            ),
        ])
            ->cookie(cookie()->forget('cart_token'))
            ->cookie(cookie()->forget('wishlist_token'));
    }
}
