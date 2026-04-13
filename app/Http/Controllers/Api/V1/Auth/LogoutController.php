<?php
namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class LogoutController extends Controller
{
    #[OA\Post(
        path: "/api/v1/logout",
        description: "Invalidate current access token",
        summary: "Logout user",
        security: [["sanctum" => []]],
        tags: ["Auth"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successfully logged out",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                            example: "Logged out successfully"
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated"
            )
        ]
    )]

    public function __invoke(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
