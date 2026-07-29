<?php

namespace Database\Factories;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => CustomerType::Individual,
            'name' => fake()->name(),
            'trade_name' => null,
            'phone' => fake()->numerify('55########'),
            'email' => fake()->unique()->safeEmail(),
            'status' => CustomerStatus::Active,
        ];
    }

    public function company(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => CustomerType::Company,
            'name' => fake()->company(),
            'trade_name' => fake()->companySuffix().' '.fake()->company(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CustomerStatus::Inactive,
        ]);
    }

    public function withoutEmail(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email' => null,
        ]);
    }
}
