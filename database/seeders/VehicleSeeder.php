<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        /** @var Collection<int, int> $customerIds */
        $customerIds = Customer::query()->pluck('id');

        /** @var Collection<int, int> $vehicleTypeIds */
        $vehicleTypeIds = VehicleType::query()->pluck('id');

        if ($customerIds->isEmpty() || $vehicleTypeIds->isEmpty()) {
            return;
        }

        Vehicle::factory()
            ->count(85)
            ->state(fn (): array => [
                'customer_id' => $customerIds->random(),
                'vehicle_type_id' => $vehicleTypeIds->random(),
            ])
            ->create();

        Vehicle::factory()
            ->count(10)
            ->inService()
            ->state(fn (): array => [
                'customer_id' => $customerIds->random(),
                'vehicle_type_id' => $vehicleTypeIds->random(),
            ])
            ->create();

        Vehicle::factory()
            ->count(5)
            ->inactive()
            ->state(fn (): array => [
                'customer_id' => $customerIds->random(),
                'vehicle_type_id' => $vehicleTypeIds->random(),
            ])
            ->create();
    }
}
