<?php

namespace App\Actions\Catalogs;

use App\Models\ServiceCategory;

class DeactivateServiceCategory
{
    public function handle(ServiceCategory $category): ServiceCategory
    {
        $category->update([
            'is_active' => false,
        ]);

        return $category->fresh();
    }
}
