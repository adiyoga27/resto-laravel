<?php

namespace Database\Factories;

use App\Models\StoreProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoreProfile>
 */
class StoreProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_name' => fake()->company().' '.fake()->randomElement(['Resto', 'Cafe', 'Bakery']),
            'store_address' => fake()->address(),
            'store_phone' => fake()->phoneNumber(),
            'is_active' => true,
        ];
    }
}
