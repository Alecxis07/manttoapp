<?php

namespace App\Actions\Catalogs;

use App\DTOs\ServiceCategoryData;
use App\Models\ServiceCategory;

class UpdateServiceCategory
{
    public function handle(ServiceCategory $category, ServiceCategoryData $data): ServiceCategory
    {
        $category->update([
            'code' => $data->code,
            'name' => $data->name,
            'description' => $data->description,
            'is_active' => $data->isActive,
        ]);

        return $category->fresh();
    }
}
