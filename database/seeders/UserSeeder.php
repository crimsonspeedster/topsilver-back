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
            'email' => 'developer@gmail.com',
            'password' => Hash::make('password'),
            'role' => UserRoles::Developer,
        ]);

        User::factory()->create([
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => UserRoles::Admin,
        ]);

        User::factory()->create([
            'email' => 'content_manager@gmail.com',
            'password' => Hash::make('password'),
            'role' => UserRoles::ContentManager,
        ]);

        User::factory()->create([
            'email' => 'shop_manager@gmail.com',
            'password' => Hash::make('password'),
            'role' => UserRoles::ShopManager,
        ]);

        User::factory()->create([
            'email' => 'customer@gmail.com',
            'password' => Hash::make('password'),
            'role' => UserRoles::Customer,
        ]);

        User::factory()->count(45)->create();
    }
}
