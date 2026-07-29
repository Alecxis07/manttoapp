<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('vehicles.read');
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        return $user->can('vehicles.read');
    }

    public function create(User $user): bool
    {
        return $user->can('vehicles.create');
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        return $user->can('vehicles.update');
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $user->can('vehicles.delete');
    }

    /**
     * Export expediente/historial PDF (RF-HIS-007).
     */
    public function exportHistory(User $user, Vehicle $vehicle): bool
    {
        return $user->can('vehicles.read');
    }
}
