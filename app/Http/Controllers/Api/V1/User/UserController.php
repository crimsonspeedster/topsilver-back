<?php
namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Get(
        path: "/api/v1/me",
        description: "Returns current authenticated user with profile, city and region",
        summary: "Get authenticated user",
        security: [["sanctum" => []]],
        tags: ["User"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Current user data",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            ref: "#/components/schemas/UserResource"
                        )
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
        return response()->json([
            'data' => new UserResource(
                $request->user()->load('profile.city.region')
            ),
        ]);
    }
}
