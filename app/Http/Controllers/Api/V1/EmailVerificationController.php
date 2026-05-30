<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer',
            'hash' => 'required|string',
        ]);

        $user = User::findOrFail($validated['id']);

        if (!hash_equals(
            sha1($user->getEmailForVerification()),
            $validated['hash']
        )) {
            abort(403, 'Invalid hash');
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return response()->json([
            'message' => 'Електронна адреса підтверджена',
        ]);
    }
}
