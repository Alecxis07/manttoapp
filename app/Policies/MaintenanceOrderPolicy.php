<?php

namespace App\Policies;

use App\Models\MaintenanceOrder;
use App\Models\User;

class MaintenanceOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('maintenance_orders.read');
    }

    public function view(User $user, MaintenanceOrder $maintenanceOrder): bool
    {
        return $user->can('maintenance_orders.read');
    }

    public function create(User $user): bool
    {
        return $user->can('maintenance_orders.create');
    }

    public function update(User $user, MaintenanceOrder $maintenanceOrder): bool
    {
        if (! $user->can('maintenance_orders.update')) {
            return false;
        }

        if (! $maintenanceOrder->isEditable() && ! $user->hasRole('admin')) {
            return false;
        }

        return true;
    }

    public function diagnose(User $user, MaintenanceOrder $maintenanceOrder): bool
    {
        return $user->can('maintenance_orders.diagnose');
    }

    public function addItems(User $user, MaintenanceOrder $maintenanceOrder): bool
    {
        return $user->can('maintenance_orders.add_items') && $maintenanceOrder->isEditable();
    }

    public function changeStatus(User $user, MaintenanceOrder $maintenanceOrder): bool
    {
        return $user->can('maintenance_orders.change_status');
    }

    public function reopen(User $user, MaintenanceOrder $maintenanceOrder): bool
    {
        return $user->can('maintenance_orders.reopen');
    }

    public function delete(User $user, MaintenanceOrder $maintenanceOrder): bool
    {
        return $user->can('maintenance_orders.delete');
    }

    public function attach(User $user, MaintenanceOrder $maintenanceOrder): bool
    {
        return $user->can('attachments.*');
    }
}
