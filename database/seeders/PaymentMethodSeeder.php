<?php

namespace Database\Seeders;

use App\Enums\PaymentMethods;
use App\Models\PaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            [
                'type' => PaymentMethods::COD,
                'name' => 'Оплата при отриманні товару'
            ],
            [
                'type' => PaymentMethods::PLATA_BY_MONO,
                'name' => 'Оплата Online by Mono',
            ],
            [
                'type' => PaymentMethods::LIQPAY,
                'name' => 'Оплата Online by Liqpay',
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::factory()->create([
                'name' => $method['name'],
                'type' => $method['type'],
            ]);
        }
    }
}
