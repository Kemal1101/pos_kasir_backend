<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(['Admin', 'Kasir', 'Gudang', 'Manager']),
            'description' => $this->faker->sentence(),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Admin',
            'description' => 'Administrator with full access',
        ]);
    }

    public function kasir(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Kasir',
            'description' => 'Cashier role for sales transactions',
        ]);
    }

    public function gudang(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Gudang',
            'description' => 'Warehouse role for inventory management',
        ]);
    }
}
