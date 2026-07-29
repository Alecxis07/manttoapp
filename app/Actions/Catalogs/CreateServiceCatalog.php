<?php

namespace App\Actions\Catalogs;

use App\DTOs\ServiceCatalogData;
use App\Models\ServiceCatalog;

class CreateServiceCatalog
{
    public function handle(ServiceCatalogData $data): ServiceCatalog
    {
        return ServiceCatalog::query()->create([
            'code' => $data->code,
            'description' => $data->description,
            'service_category_id' => $data->serviceCategoryId,
            'type' => $data->type,
            'base_price' => $data->basePrice,
            'unit_of_measure' => $data->unitOfMeasure,
            'estimated_minutes' => $data->estimatedMinutes,
            'is_active' => $data->isActive,
        ]);
    }
}
