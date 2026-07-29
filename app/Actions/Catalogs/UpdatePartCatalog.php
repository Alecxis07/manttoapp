<?php

namespace App\Actions\Catalogs;

use App\DTOs\PartCatalogData;
use App\Models\PartCatalog;

class UpdatePartCatalog
{
    public function handle(PartCatalog $part, PartCatalogData $data): PartCatalog
    {
        $part->update([
            'code' => $data->code,
            'description' => $data->description,
            'type' => $data->type,
            'service_category_id' => $data->serviceCategoryId,
            'base_price' => $data->basePrice,
            'unit_of_measure' => $data->unitOfMeasure,
            'is_active' => $data->isActive,
        ]);

        return $part->fresh(['category']);
    }
}
