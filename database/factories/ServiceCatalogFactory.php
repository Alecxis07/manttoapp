<?php

namespace Database\Factories;

use App\Enums\ServiceCatalogType;
use App\Models\ServiceCatalog;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceCatalog>
 */
class ServiceCatalogFactory extends Factory
{
    protected $model = ServiceCatalog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('SRV-###')),
            'description' => fake()->sentence(3),
            'service_category_id' => ServiceCategory::factory(),
            'type' => fake()->randomElement(ServiceCatalogType::cases()),
            'base_price' => fake()->randomFloat(2, 50, 5000),
            'unit_of_measure' => fake()->randomElement(['servicio', 'hora', 'unidad']),
            'estimated_minutes' => fake()->optional()->numberBetween(15, 480),
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
