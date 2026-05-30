<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ResendEmailVerificationController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'Користувача не знайдено'
            ], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Електронна адреса вже підтверджена'
            ], 403);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Електронний лист для підтвердження надіслано'
        ]);
    }
}
