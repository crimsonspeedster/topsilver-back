<?php
namespace App\Instagram\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\InstagramAccountResource;
use App\Models\InstagramAccount;
use App\Services\Instagram\InstagramApiService;
use App\Services\Instagram\SocialFeedService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InstagramController extends Controller
{
    public function getData()
    {
        $instagram_account = InstagramAccount::firstOrFail();

        return response()->json([
            'data' => new InstagramAccountResource($instagram_account),
        ]);
    }

    public function remove()
    {
        $instagram_account = InstagramAccount::firstOrFail();
        $instagram_account->delete();

        return response()->json([
            'success' => true,
            'message' => 'Instagram account removed.',
        ]);
    }

    public function auth(SocialFeedService $socialFeedService)
    {
        $oauth_state = Str::random(40);
        $state_query = http_build_query([
            'oauth_state' => $oauth_state,
        ]);
        $state = route('instagram.callback') . '?' . $state_query;

        session([
            'instagram_oauth_state' => $oauth_state,
            'instagram_tool_url' => url()->previous(),
        ]);

        $query = $socialFeedService->getOauthURLParams(
            config('services.instagram.app_id'),
            $state
        );

        return redirect()->to(
            'https://www.instagram.com/oauth/authorize?'.$query
        );
    }

    public function callback(Request $request, InstagramApiService $api)
    {
        abort_unless(
            $request->oauth_state === session('instagram_oauth_state'),
            403
        );

        $account = $request->input('accounts.0');

        if (!$account) {
            abort(400, 'Instagram account not found');
        }

        $access_token = $account['access_token'];

        try {
            $response = $api->getMe($access_token);

            $username = $response['username'];
            $name = $response['name'];
            $instagram_id = $account['id'];
            $expires_in = intval($account['expires_in']);

            InstagramAccount::updateOrCreate(
                [
                    'instagram_id' => $instagram_id,
                ],
                [
                    'username' => $username,
                    'name' => $name,
                    'access_token' => $access_token,
                    'token_expires_at' => now()->addSeconds($expires_in),
                ]
            );

            return redirect()->to(
                session('instagram_tool_url')
            );
        }
        catch (ConnectionException|RequestException $e) {
            abort(400, $e->getMessage());
        }
    }
}
