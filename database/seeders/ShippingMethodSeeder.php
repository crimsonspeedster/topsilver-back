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
        $methods = [
            [
                'type' => ShippingMethods::NOVA_POSHTA_COURIER,
                'name' => "Нова пошта (кур'єр)"
            ],
            [
                'type' => ShippingMethods::NOVA_POSHTA_WAREHOUSE,
                'name' => 'Нова пошта (до відділення)',
            ],
            [
                'type' => ShippingMethods::LOCAL_PICKUP,
                'name' => 'Самовивіз',
            ],
        ];

        foreach ($methods as $method) {
            ShippingMethod::factory()->create([
                'name' => $method['name'],
                'type' => $method['type'],
            ]);
        }
    }
}
