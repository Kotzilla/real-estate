<?php

namespace Database\Factories;

use App\Enums\ContractType;
use App\Models\Contract;
use App\Models\Person;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contract>
 */
class ContractFactory extends Factory
{
    /**
     * Define the model's default state: a 12-month lease, a draft
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'customer_id' => Person::factory(),
            'type' => ContractType::Rent,
            'months' => 12,
            'price' => 50000,
            'deposit' => 100000,
            'advance' => 50000,
            'payment_day' => 1,
            'commission_percent' => 8.33,
            'signed_on' => now()->toDateString(),
            'start_on' => now()->toDateString(),
            'end_on' => now()->addYear()->subDay()->toDateString(),
        ];
    }

    /**
     * A sale with transfer in two months
     *
     * @return static
     */
    public function sale(): static
    {
        return $this->state(fn () => [
            'type' => ContractType::Sale,
            'months' => null,
            'price' => 12000000,
            'deposit' => 1200000,
            'advance' => 0,
            'payment_day' => null,
            'commission_percent' => 3,
            'end_on' => now()->addMonths(2)->toDateString(),
        ]);
    }
}
