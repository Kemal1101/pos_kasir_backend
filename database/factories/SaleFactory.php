<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SaleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'payment_id' => null,
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'payment_status' => 'draft',
            'sale_date' => now(),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'paid',
            'payment_id' => \App\Models\Payment::factory(),
        ]);
    }

    public function withItems(): static
    {
        return $this->state(fn (array $attributes) => [
            'subtotal' => $this->faker->numberBetween(100000, 1000000),
            'discount_amount' => $this->faker->numberBetween(0, 50000),
            'tax_amount' => $this->faker->numberBetween(10000, 50000),
        ])->afterCreating(function ($sale) {
            $sale->update([
                'total_amount' => $sale->subtotal - $sale->discount_amount + $sale->tax_amount,
            ]);
        });
    }
}
