<?php

namespace App\Actions\Catalogs;

use App\DTOs\ServiceCategoryData;
use App\Models\ServiceCategory;

class CreateServiceCategory
{
    public function handle(ServiceCategoryData $data): ServiceCategory
    {
        return ServiceCategory::query()->create([
            'code' => $data->code,
            'name' => $data->name,
            'description' => $data->description,
            'is_active' => $data->isActive,
        ]);
    }
}
