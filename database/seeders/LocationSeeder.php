<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            'header',
            'mobile',
            'footer_first',
            'footer_second',
            'footer_third',
        ];

        foreach ($locations as $location) {
            Location::factory()->create([
                'name' => $location,
            ]);
        }
    }
}
