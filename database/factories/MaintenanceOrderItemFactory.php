<?php

namespace Database\Factories;

use App\Models\MaintenanceOrder;
use App\Models\MaintenanceOrderItem;
use App\Models\ServiceCatalog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceOrderItem>
 */
class MaintenanceOrderItemFactory extends Factory
{
    protected $model = MaintenanceOrderItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 5);
        $unitPrice = fake()->randomFloat(2, 50, 2500);
        $discount = 0;
        $lineTotal = round(($quantity * $unitPrice) - $discount, 2);

        return [
            'maintenance_order_id' => MaintenanceOrder::factory(),
            'service_catalog_id' => ServiceCatalog::factory(),
            'code' => fake()->bothify('SRV-###'),
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
