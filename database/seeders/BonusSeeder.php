<?php

namespace Database\Seeders;

use App\Models\Bonus;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BonusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            Bonus::factory()
                ->count(rand(1, 5))
                ->create([
                    'user_id' => $user->id,
                ]);
        }
    }
}
