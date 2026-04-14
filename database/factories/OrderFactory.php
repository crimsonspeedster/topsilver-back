<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethods;
use App\Enums\ShippingMethods;
use App\Models\Order;
use App\Models\User;
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

        $user_id = User::query()->inRandomOrder()->value('id');
        $user_id = $this->faker->boolean(70)
            ? $user_id
            : null;

        $payment_method = $this->faker->randomElement(PaymentMethods::cases());
        $shipping_method = $this->faker->randomElement(ShippingMethods::cases());

        if ($payment_method === PaymentMethods::COD) {
            $status = $this->faker->randomElement(
                array_map(fn($case) => $case->value, OrderStatus::forFactory())
            );
            $paid_at = null;
        }
        else {
            $status = $this->faker->randomElement(OrderStatus::cases());
            $paid_at = $status !== OrderStatus::PENDING_PAYMENT ? now() : null;
        }

        return [
            'status' => $status,
            'subtotal' => $this->faker->numberBetween(3000, 50000),
            'total' => $this->faker->numberBetween(3000, 50000),
            'paid_at' => $paid_at,
            'notes' => $this->faker->text(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'middle_name' => $this->faker->lastName(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->email(),
            'shipping_type' => $shipping_method,
            'shipping_data' => [],
            'payment_type' => $payment_method,
            'payment_data' => [],
            'user_id' => $user_id,
        ];
    }
}
