<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerFiscalProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerFiscalProfile>
 */
class CustomerFiscalProfileFactory extends Factory
{
    protected $model = CustomerFiscalProfile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'legal_name' => fake()->company(),
            'rfc' => strtoupper(fake()->bothify('???######???')),
            'tax_regime_code' => '601',
            'cfdi_use_code' => 'G03',
            'postal_code' => fake()->numerify('#####'),
            'email' => fake()->safeEmail(),
            'is_default' => true,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_default' => true,
        ]);
    }

    public function secondary(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_default' => false,
        ]);
    }
}
