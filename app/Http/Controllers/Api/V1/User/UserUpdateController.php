<?php
namespace App\Http\Controllers\Api\V1\User;

use App\Enums\SexTypes;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class UserUpdateController extends Controller
{
    public function profile(Request $request)
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
                'name' => $data['name'],
                'surname' => $data['surname'],
                'middle_name' => $data['middle_name'],
                'about' => $data['about'],
                'sex' => $data['sex'],
                'dob' => $data['dob'],
                'city_id' => $data['city_id'],
            ]);

            $profile->save();

            return $authUser->fresh();
        });

        return response()->json([
            'data' => new UserResource(
                $user->load('profile.city.region')
            ),
        ]);
    }

    public function password(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', session()->getId())
            ->delete();

        return response()->json([
            'data' => new UserResource(
                $user->load('profile.city.region')
            ),
        ]);
    }
}
