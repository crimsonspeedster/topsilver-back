<?php
namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\SexTypes;
use App\Enums\UserRoles;
use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;

class RegisterController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'email' => [
                'required',
                'email',
                'unique:users,email',
            ],
            'phone' => [
                'required',
                'string',
                'unique:users,phone',
                'regex:/^\+?[0-9]{9,15}$/',
            ],
            'password' => [
                'required',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'surname' => ['required', 'string', 'min:2', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string', 'max:1000'],
            'sex' => [
                'nullable',
               new Enum(SexTypes::class),
            ],
            'dob' => [
                'nullable',
                'date',
                'date_format:Y-m-d',
                'before:today',
            ],
            'city_id' => ['nullable', 'exists:cities,id'],
        ]);

        $result = DB::transaction(function () use ($data) {
            $user = User::create([
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'role' => UserRoles::Customer,
            ]);

            $user->profile()->create([
                'name' => $data['name'],
                'surname' => $data['surname'],
                'middle_name' => $data['middle_name'] ?? null,
                'about' => $data['about'] ?? null,
                'sex' => $data['sex'] ?? null,
                'dob' => $data['dob'] ?? null,
                'city_id' => $data['city_id'] ?? null,
            ]);

            $token = $user->createToken(
                'site_token',
                [],
                now()->addDays(7)
            )->plainTextToken;

            return compact('user', 'token');
        });

        event(new Registered($result['user']));
        event(new UserRegistered($result['user']));

        return response()->json([
            'data' => new UserResource(
                $result['user']->load('profile.city.region')
            ),
        ], 201)->cookie(
            'access_token',
            $result['token'],
            60 * 24 * 7,
            '/',
            null,
            true,
            true,
            false,
            'Strict'
        );
    }
}
