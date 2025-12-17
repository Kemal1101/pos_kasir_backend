<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleItemFactory extends Factory
{
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 5);
        $price = $this->faker->numberBetween(50000, 500000);
        $subtotal = $quantity * $price;

        return [
            'sale_id' => Sale::factory(),
            'product_id' => Product::factory(),
            'name_product' => $this->faker->words(3, true),
            'quantity' => $quantity,
            'discount_amount' => 0,
            'subtotal' => $subtotal,
        ];
    }

    public function withDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_amount' => $this->faker->numberBetween(5000, 50000),
        ]);
    }
}
