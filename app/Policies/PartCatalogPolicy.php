<?php

namespace App\Policies;

use App\Models\PartCatalog;
use App\Models\User;

class PartCatalogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('part_catalog.read');
    }

    public function view(User $user, PartCatalog $partCatalog): bool
    {
        return $user->can('part_catalog.read');
    }

    public function create(User $user): bool
    {
        return $user->can('part_catalog.create');
    }

    public function update(User $user, PartCatalog $partCatalog): bool
    {
        return $user->can('part_catalog.update');
    }

    public function delete(User $user, PartCatalog $partCatalog): bool
    {
        return $user->can('part_catalog.delete');
    }
}
