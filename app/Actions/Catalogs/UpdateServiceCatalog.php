<?php

namespace App\Actions\Catalogs;

use App\DTOs\ServiceCatalogData;
use App\Models\ServiceCatalog;

class UpdateServiceCatalog
{
    public function handle(ServiceCatalog $service, ServiceCatalogData $data): ServiceCatalog
    {
        $service->update([
            'code' => $data->code,
            'description' => $data->description,
            'service_category_id' => $data->serviceCategoryId,
            'type' => $data->type,
            'base_price' => $data->basePrice,
            'unit_of_measure' => $data->unitOfMeasure,
            'estimated_minutes' => $data->estimatedMinutes,
            'is_active' => $data->isActive,
        ]);

        return $service->fresh(['category']);
    }
}
