<?php
namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SendEmailVerificationController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Електронна адреса вже підтверджена',
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Лист для підтвердження електронної адреси надіслано повторно',
        ]);
    }
}
