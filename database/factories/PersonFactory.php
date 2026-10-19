<?php

namespace Database\Factories;

use App\Enums\PersonType;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Person>
 */
class PersonFactory extends Factory
{
    /**
     * Define the model's default state
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => PersonType::Customer,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'nationality' => fake()->randomElement(['British', 'German', 'Australian', 'Russian', 'Thai']),
            'passport_no' => strtoupper(fake()->bothify('??#######')),
            'phone' => fake()->numerify('08########'),
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->streetAddress(),
        ];
    }

    /**
     * A Thai owner with a bank account for the rent
     *
     * @return static
     */
    public function owner(): static
    {
        return $this->state(fn () => [
            'type' => PersonType::Owner,
            'first_name_th' => 'สมชาย',
            'last_name_th' => 'ใจดี',
            'nationality' => 'Thai',
            'id_card_no' => fake()->numerify('#############'),
            'passport_no' => null,
            'address_th' => '12/3 หมู่ 4 ต.ราไวย์ อ.เมือง จ.ภูเก็ต',
            'bank_name' => 'Kasikorn Bank',
            'bank_account_name' => 'Somchai Jaidee',
            'bank_account_no' => fake()->numerify('###-#-#####-#'),
        ]);
    }
}
