<?php

namespace App\Actions\Vehicles;

use App\Enums\VehicleStatus;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Validation\ValidationException;

class DeleteVehicle
{
    /**
     * Soft-delete when the unit has no maintenance orders (RN-UNI-003).
     * Units in service cannot be deleted (RF-UNI-005).
     */
    public function handle(Vehicle $vehicle, User $actor): void
    {
        if ($vehicle->status === VehicleStatus::InService) {
            throw ValidationException::withMessages([
                'vehicle' => __('No se puede eliminar una unidad en servicio. Cámbiela de estatus primero.'),
            ]);
        }

        if ($vehicle->isInUse()) {
            throw ValidationException::withMessages([
                'vehicle' => __('No se puede eliminar una unidad con órdenes de mantenimiento asociadas. Desactívela en su lugar.'),
            ]);
        }

        $vehicle->update(['updated_by' => $actor->id]);
        $vehicle->delete();
    }
}
