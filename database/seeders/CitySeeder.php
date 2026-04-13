<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Region;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Kyiv Oblast' => [
                'Kyiv',
                'Boryspil',
                'Bila Tserkva',
                'Irpin',
                'Brovary',
            ],

            'Lviv Oblast' => [
                'Lviv',
                'Drohobych',
                'Stryi',
                'Chervonohrad',
            ],

            'Odesa Oblast' => [
                'Odesa',
                'Izmail',
                'Chornomorsk',
                'Yuzhne',
            ],

            'Kharkiv Oblast' => [
                'Kharkiv',
                'Izium',
                'Chuhuiv',
            ],

            'Dnipropetrovsk Oblast' => [
                'Dnipro',
                'Kryvyi Rih',
                'Kamianske',
                'Nikopol',
            ],

            'Vinnytsia Oblast' => [
                'Vinnytsia',
                'Mohyliv-Podilskyi',
                'Haisyn',
            ],

            'Poltava Oblast' => [
                'Poltava',
                'Kremenchuk',
                'Lubny',
            ],

            'Chernihiv Oblast' => [
                'Chernihiv',
                'Nizhyn',
            ],

            'Zhytomyr Oblast' => [
                'Zhytomyr',
                'Berdychiv',
            ],

            'Ivano-Frankivsk Oblast' => [
                'Ivano-Frankivsk',
                'Kalush',
            ],
        ];

        foreach ($data as $regionName => $cities) {

            $region = Region::where('name', $regionName)->first();

            if (!$region) {
                continue;
            }

            foreach ($cities as $cityName) {
                City::firstOrCreate([
                    'name' => $cityName,
                    'region_id' => $region->id,
                ]);
            }
        }
    }
}
