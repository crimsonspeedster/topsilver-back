<?php

namespace Database\Seeders;

use App\Enums\ShippingMethods;
use App\Models\ShippingMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (ShippingMethods::cases() as $method) {
            ShippingMethod::factory()->create([
                'type' => $method,
            ]);
        }
    }
}
