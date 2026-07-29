<?php

namespace App\Policies;

use App\Models\ServiceCategory;
use App\Models\User;

class ServiceCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('service_categories.read');
    }

    public function view(User $user, ServiceCategory $serviceCategory): bool
    {
        return $user->can('service_categories.read');
    }

    public function create(User $user): bool
    {
        return $user->can('service_categories.create');
    }

    public function update(User $user, ServiceCategory $serviceCategory): bool
    {
        return $user->can('service_categories.update');
    }

    public function delete(User $user, ServiceCategory $serviceCategory): bool
    {
        return $user->can('service_categories.delete');
    }
}
