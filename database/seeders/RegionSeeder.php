<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = [
            'Kyiv Oblast',
            'Vinnytsia Oblast',
            'Volyn Oblast',
            'Dnipropetrovsk Oblast',
            'Donetsk Oblast',
            'Zhytomyr Oblast',
            'Zakarpattia Oblast',
            'Zaporizhzhia Oblast',
            'Ivano-Frankivsk Oblast',
            'Kirovohrad Oblast',
            'Lviv Oblast',
            'Mykolaiv Oblast',
            'Odesa Oblast',
            'Poltava Oblast',
            'Rivne Oblast',
            'Sumy Oblast',
            'Ternopil Oblast',
            'Kharkiv Oblast',
            'Kherson Oblast',
            'Khmelnytskyi Oblast',
            'Cherkasy Oblast',
            'Chernihiv Oblast',
            'Chernivtsi Oblast',
            'Kyiv City',
        ];

        foreach ($regions as $region) {
            Region::firstOrCreate(['name' => $region]);
        }
    }
}
