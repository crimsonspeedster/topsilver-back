<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Shop;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = City::inRandomOrder()
            ->take(10)
            ->pluck('id')
            ->toArray();

        foreach ($cities as $cityId) {
            Shop::factory()->count(rand(1, 5))->create([
                'city_id' => $cityId,
            ]);
        }
    }
}
