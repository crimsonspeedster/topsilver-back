<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CitySeeder::class,
            PaymentMethodSeeder::class,
            ShippingMethodSeeder::class,
            LocationSeeder::class,
            SettingsSeeder::class,
        ]);
    }
}
