<?php

namespace Database\Factories;

use App\Models\VehicleType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleType>
 */
class VehicleTypeFactory extends Factory
{
    protected $model = VehicleType::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Tractocamión',
            'Remolque',
            'Plataforma',
            'Tolva',
            'Caja seca',
            'Rabón',
            'Torton',
        ]).' '.fake()->unique()->numerify('####');

        return [
            'code' => strtoupper(fake()->unique()->bothify('VT-####??')),
            'name' => $name,
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
