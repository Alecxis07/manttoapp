<?php

namespace Database\Factories;

use App\Models\MaintenanceOrder;
use App\Models\MaintenancePart;
use App\Models\PartCatalog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenancePart>
 */
class MaintenancePartFactory extends Factory
{
    protected $model = MaintenancePart::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 10);
        $unitPrice = fake()->randomFloat(2, 20, 1500);
        $discount = 0;
        $lineTotal = round(($quantity * $unitPrice) - $discount, 2);

        return [
            'maintenance_order_id' => MaintenanceOrder::factory(),
            'part_catalog_id' => PartCatalog::factory(),
            'code' => fake()->bothify('PRT-###'),
            'description' => fake()->sentence(3),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount' => $discount,
            'line_total' => $lineTotal,
            'notes' => null,
            'sort_order' => 0,
        ];
    }
}
