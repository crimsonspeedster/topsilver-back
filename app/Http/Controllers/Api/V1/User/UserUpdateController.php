<?php
namespace App\Http\Controllers\Api\V1\User;

use App\Enums\SexTypes;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UserUpdateController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => [
                'sometimes',
                'string',
                'regex:/^\+?[0-9]{9,15}$/',
                Rule::unique('users', 'phone')->ignore($user->id),
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

        $user = DB::transaction(function () use ($user, $data) {
            $user->update(array_filter([
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]));

            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $data['name'] ?? $user->profile->name,
                    'surname' => $data['surname'] ?? $user->profile->surname,
                    'middle_name' => $data['middle_name'] ?? null,
                    'about' => $data['about'] ?? null,
                    'sex' => $data['sex'] ?? null,
                    'dob' => $data['dob'] ?? null,
                    'city_id' => $data['city_id'] ?? null,
                ]
            );

            return $user->load('profile.city.region');
        });

        return response()->json([
            'user' => new UserResource(
                $request->user()->load('profile.city.region')
            ),
        ]);
    }
}
