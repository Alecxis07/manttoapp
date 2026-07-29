<?php

namespace Database\Factories;

use App\Enums\MaintenanceOrderStatus;
use App\Enums\MaintenanceOrderType;
use App\Models\Customer;
use App\Models\MaintenanceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceOrder>
 */
class MaintenanceOrderFactory extends Factory
{
    protected $model = MaintenanceOrder::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'folio' => sprintf('ORD-%d-%05d', (int) date('Y'), fake()->unique()->numberBetween(1, 99999)),
            'customer_id' => Customer::factory(),
            'vehicle_id' => Vehicle::factory(),
            'quotation_id' => null,
            'type' => fake()->randomElement(MaintenanceOrderType::cases()),
            'status' => MaintenanceOrderStatus::Received,
            'received_at' => now(),
            'mileage' => fake()->numberBetween(0, 300000),
            'reason' => fake()->sentence(),
            'diagnosis' => null,
            'technical_notes' => null,
            'subtotal' => '0.00',
            'discount_total' => '0.00',
            'tax_total' => '0.00',
            'total' => '0.00',
            'tax_rate' => '16.00',
            'assigned_user_id' => null,
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (MaintenanceOrder $order): void {
            $vehicle = Vehicle::query()->find($order->vehicle_id);

            if ($vehicle !== null && (int) $order->customer_id !== (int) $vehicle->customer_id) {
                $order->forceFill(['customer_id' => $vehicle->customer_id])->saveQuietly();
            }
        });
    }

    public function forVehicle(Vehicle $vehicle): static
    {
        return $this->state(fn (array $attributes): array => [
            'customer_id' => $vehicle->customer_id,
            'vehicle_id' => $vehicle->id,
        ]);
    }

    public function withDiagnosis(string $diagnosis = 'Diagnóstico técnico registrado.'): static
    {
        return $this->state(fn (array $attributes): array => [
            'diagnosis' => $diagnosis,
        ]);
    }

    public function assignedTo(User $user): static
    {
        return $this->state(fn (array $attributes): array => [
            'assigned_user_id' => $user->id,
        ]);
    }

    public function status(MaintenanceOrderStatus $status): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => $status,
        ]);
    }
}
