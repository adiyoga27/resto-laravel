<?php

namespace Database\Factories;

use App\Enums\TableStatus;
use App\Models\RestaurantTable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RestaurantTable>
 */
class RestaurantTableFactory extends Factory
{
    public function definition(): array
    {
        return [
            'table_number' => fake()->unique()->bothify('A-##'),
            'capacity' => fake()->numberBetween(2, 8),
            'status' => TableStatus::Kosong,
        ];
    }
}
