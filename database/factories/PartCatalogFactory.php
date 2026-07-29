<?php

namespace Database\Factories;

use App\Enums\PartCatalogType;
use App\Models\PartCatalog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartCatalog>
 */
class PartCatalogFactory extends Factory
{
    protected $model = PartCatalog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('PRT-###')),
            'description' => fake()->sentence(3),
            'type' => fake()->randomElement(PartCatalogType::cases()),
            'service_category_id' => null,
            'base_price' => fake()->randomFloat(2, 10, 8000),
            'unit_of_measure' => fake()->randomElement(['pieza', 'litro', 'metro', 'kit']),
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
