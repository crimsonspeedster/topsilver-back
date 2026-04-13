<?php
namespace App\Http\Controllers\Api\V1\User;

use App\Enums\SexTypes;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Attributes as OA;

class UserUpdateController extends Controller
{
    #[OA\Patch(
        path: "/api/v1/me",
        description: "Partially updates user and profile data",
        summary: "Update authenticated user",
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "user",
                        ref: "#/components/schemas/UpdateProfileRequestResource"
                    )
                ]
            )
        ),
        tags: ["User"],
        responses: [
            new OA\Response(
                response: 200,
                description: "User updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "user",
                            ref: "#/components/schemas/UserResource"
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated"
            ),
            new OA\Response(
                response: 422,
                description: "Validation error"
            )
        ]
    )]
    public function __invoke(Request $request)
    {
        $authUser = $request->user();

        $data = $request->validate([
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users', 'email')->ignore($authUser->id),
            ],
            'phone' => [
                'sometimes',
                'string',
                'regex:/^\+?[0-9]{9,15}$/',
                Rule::unique('users', 'phone')->ignore($authUser->id),
            ],
            'name' => ['sometimes', 'string', 'min:2', 'max:255'],
            'surname' => ['sometimes', 'string', 'min:2', 'max:255'],
            'middle_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'about' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'sex' => [
                'sometimes',
                'nullable',
                new Enum(SexTypes::class),
            ],
            'dob' => [
                'sometimes',
                'nullable',
                'date',
                'date_format:Y-m-d',
                'before:today',
            ],
            'city_id' => [
                'sometimes',
                'nullable',
                'exists:cities,id',
            ],
        ]);

        $user = DB::transaction(function () use ($authUser, $data) {
            $authUser->update(array_filter([
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]));

            $profile = $authUser->profile()->first() ?? new Profile();

            $profile->fill([
                'name' => $data['name'] ?? $profile->name,
                'surname' => $data['surname'] ?? $profile->surname,
                'middle_name' => $data['middle_name'] ?? $profile->middle_name,
                'about' => $data['about'] ?? $profile->about,
                'sex' => $data['sex'] ?? $profile->sex,
                'dob' => $data['dob'] ?? $profile->dob,
                'city_id' => $data['city_id'] ?? $profile->city_id,
            ]);

            $profile->save();

            return $authUser->fresh();
        });

        return response()->json([
            'user' => new UserResource(
                $user->load('profile.city.region')
            ),
        ]);
    }
}
