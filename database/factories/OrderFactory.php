<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\ShippingMethod;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(OrderStatus::cases());
        $payment_method = $this->faker->randomElement(PaymentMethod::cases());
        $shipping_method = $this->faker->randomElement(ShippingMethod::cases());

        return [
            'total' => $this->faker->numberBetween(3000, 50000),
            'shipping_method' => $shipping_method,
            'payment_method' => $payment_method,
            'status' => $status,
        ];
    }
}
