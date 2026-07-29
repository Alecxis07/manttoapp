<?php

namespace App\Actions\Customers;

use App\Enums\CustomerStatus;
use App\Enums\VehicleStatus;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class DeactivateCustomer
{
    public function handle(Customer $customer, User $actor): Customer
    {
        $this->ensureNoActiveVehicles($customer);

        $customer->update([
            'status' => CustomerStatus::Inactive,
            'updated_by' => $actor->id,
        ]);

        return $customer->fresh(['fiscalProfiles']);
    }

    /**
     * Block deactivation when the customer has active vehicles (RN-CLI-002 / RN-UNI-005).
     */
    protected function ensureNoActiveVehicles(Customer $customer): void
    {
        $hasActiveVehicles = $customer->vehicles()
            ->whereIn('status', [
                VehicleStatus::Active->value,
                VehicleStatus::InService->value,
            ])
            ->exists();

        if ($hasActiveVehicles) {
            throw ValidationException::withMessages([
                'customer' => __('No se puede desactivar un cliente con unidades activas asociadas.'),
            ]);
        }
    }
}
