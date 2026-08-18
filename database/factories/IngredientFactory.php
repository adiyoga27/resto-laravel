<?php

namespace Database\Factories;

use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ingredient>
 */
class IngredientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'unit' => fake()->randomElement(['kg', 'gram', 'liter', 'pcs']),
            'current_stock' => fake()->randomFloat(2, 1, 100),
            'min_stock' => fake()->randomFloat(2, 0, 10),
            'cost_price' => fake()->randomFloat(2, 1000, 50000),
            'is_active' => true,
        ];
    }
}
