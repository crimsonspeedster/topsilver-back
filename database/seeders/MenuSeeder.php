<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Menu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = Location::pluck('id')->toArray();

        foreach ($locations as $locationId) {
            Menu::factory()->create([
                'location_id' => $locationId
            ]);
        }
    }
}
