<?php

namespace Database\Seeders;

use App\Enums\UserRoles;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'email' => config('app.developer.email'),
            'password' => Hash::make(config('app.developer.password')),
            'phone' => config('app.developer.phone'),
            'role' => UserRoles::Developer,
        ]);

        User::factory()->create([
            'email' => config('app.admin.email'),
            'password' => Hash::make(config('app.admin.password')),
            'role' => UserRoles::Admin,
            'phone' => config('app.admin.phone'),
        ]);
    }
}
