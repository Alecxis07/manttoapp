<?php

namespace Database\Factories;

use App\Enums\VehicleStatus;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $plate = strtoupper(fake()->unique()->bothify('???-##-##'));

        return [
            'customer_id' => Customer::factory(),
            'vehicle_type_id' => VehicleType::factory(),
            'license_plate' => $plate,
            'vin' => strtoupper(fake()->unique()->bothify('1HGBH41JXMN#######')),
            'economic_number' => fake()->optional()->numerify('ECO-####'),
            'brand' => fake()->randomElement(['Kenworth', 'Freightliner', 'International', 'Volvo', 'Scania']),
            'model' => fake()->bothify('T###'),
            'year' => fake()->numberBetween(2005, (int) date('Y')),
            'engine_type' => fake()->optional()->randomElement(['Diésel', 'Gas natural']),
            'current_mileage' => fake()->numberBetween(0, 500000),
            'status' => VehicleStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => VehicleStatus::Inactive,
        ]);
    }

    public function inService(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => VehicleStatus::InService,
        ]);
    }

    public function withoutVin(): static
    {
        return $this->state(fn (array $attributes): array => [
            'vin' => null,
        ]);
    }
}
