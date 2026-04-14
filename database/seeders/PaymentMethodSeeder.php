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
        foreach (PaymentMethods::cases() as $method) {
            PaymentMethod::factory()->create([
                'type' => $method,
            ]);
        }
    }
}
