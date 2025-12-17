<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'payment_method' => $this->faker->randomElement(['cash', 'credit_card', 'bank_transfer', 'e-wallet']),
            'order_id' => 'ORDER-' . time() . '-' . $this->faker->randomNumber(6),
            'gross_amount' => $this->faker->numberBetween(50000, 1000000),
            'transaction_status' => 'pending',
            'snap_token' => null,
            'metadata' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_status' => 'settlement',
            'snap_token' => 'snap-' . $this->faker->uuid(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_status' => 'failed',
        ]);
    }
}
