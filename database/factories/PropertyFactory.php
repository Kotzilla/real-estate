<?php

namespace Database\Factories;

use App\Enums\PropertyType;
use App\Models\Person;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state: a villa for rent and for sale
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => Person::factory()->owner(),
            'type' => PropertyType::Villa,
            'name' => fake()->randomElement(['Sea View', 'Palm', 'Lagoon', 'Hillside', 'Sunset']).' Villa '.fake()->numberBetween(1, 99),
            'location' => fake()->randomElement(['Rawai', 'Kata', 'Kamala', 'Bang Tao', 'Chalong']),
            'address' => fake()->streetAddress(),
            'bedrooms' => fake()->numberBetween(1, 5),
            'bathrooms' => fake()->numberBetween(1, 4),
            'area' => fake()->numberBetween(60, 400),
            'sale_price' => fake()->numberBetween(5, 40) * 1000000,
            'rent_price' => fake()->numberBetween(30, 150) * 1000,
            'is_sale' => true,
            'is_rent' => true,
        ];
    }
}
