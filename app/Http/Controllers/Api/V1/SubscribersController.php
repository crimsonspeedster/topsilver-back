<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscribersController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $subscriber = Subscriber::firstOrCreate(
            ['email' => $data['email']],
            [
                'unsubscribe_token' => Str::uuid(),
                'is_active' => true,
            ]
        );

        if (!$subscriber->wasRecentlyCreated && !$subscriber->is_active) {
            $subscriber->update([
                'is_active' => true,
            ]);
        }

        return response()->json([
            'message' => 'Підписка оформлена успішно',
        ]);
    }

    public function unsubscribe(string $token)
    {
        $subscriber = Subscriber::where('unsubscribe_token', $token)->firstOrFail();

        $subscriber->update([
            'is_active' => false,
        ]);

        return response()->json([
            'message' => 'Підписку скасовано',
        ]);
    }
}
