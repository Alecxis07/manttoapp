<?php

namespace App\Policies;

use App\Models\ServiceCatalog;
use App\Models\User;

class ServiceCatalogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('service_catalog.read');
    }

    public function view(User $user, ServiceCatalog $serviceCatalog): bool
    {
        return $user->can('service_catalog.read');
    }

    public function create(User $user): bool
    {
        return $user->can('service_catalog.create');
    }

    public function update(User $user, ServiceCatalog $serviceCatalog): bool
    {
        return $user->can('service_catalog.update');
    }

    public function delete(User $user, ServiceCatalog $serviceCatalog): bool
    {
        return $user->can('service_catalog.delete');
    }
}
