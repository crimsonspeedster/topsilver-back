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
                'name' => 'Оплата при отриманні товару',
                'config' => [],
            ],
            [
                'type' => PaymentMethods::PLATA_BY_MONO,
                'name' => 'Оплата Online by Mono',
                'config' => [
                    'monobank_token' => config("services.monobank_token"),
                ],
            ],
            [
                'type' => PaymentMethods::LIQPAY,
                'name' => 'Оплата Online by Liqpay',
                'config' => [
                    'public_key' => config('services.liqpay.public_key'),
                    'private_key' => config('services.liqpay.private_key'),
                ],
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::factory()->create([
                'name' => $method['name'],
                'type' => $method['type'],
                'config' => $method['config'],
            ]);
        }
    }
}
