<?php

namespace Database\Factories;

use App\Models\AdditionalCost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdditionalCost>
 */
class AdditionalCostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Service Charge', 'Air Minum', 'Tissue', 'Keranjang']),
            'rate' => fake()->randomFloat(1, 1, 15),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
