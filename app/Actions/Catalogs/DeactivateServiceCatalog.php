<?php

namespace App\Actions\Catalogs;

use App\Models\ServiceCatalog;

class DeactivateServiceCatalog
{
    public function handle(ServiceCatalog $service): ServiceCatalog
    {
        $service->update([
            'is_active' => false,
        ]);

        return $service->fresh(['category']);
    }
}
