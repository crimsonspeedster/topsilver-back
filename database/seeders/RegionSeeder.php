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
            "Київська область",
            "Вінницька область",
            "Волинська область",
            "Дніпропетровська область",
            "Донецька область",
            "Житомирська область",
            "Закарпатська область",
            "Запорізька область",
            "Івано-Франківська область",
            "Кіровоградська область",
            "Крим",
            "Луганська область",
            "Львівська область",
            "Миколаївська область",
            "Одеська область",
            "Полтавська область",
            "Рівненська область",
            "Сумська область",
            "Тернопільська область",
            "Харківська область",
            "Херсонська область",
            "Хмельницька область",
            "Черкаська область",
            "Чернівецька область",
            "Чернігівська область"
        ];

        foreach ($regions as $region) {
            Region::firstOrCreate(['name' => $region]);
        }
    }
}
