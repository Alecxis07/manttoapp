<?php

namespace App\Actions\Catalogs;

use App\Models\ServiceCatalog;
use Illuminate\Validation\ValidationException;

class DeleteServiceCatalog
{
    /**
     * RN-CAT-001: physically delete only when not in use; otherwise reject.
     */
    public function handle(ServiceCatalog $service): void
    {
        if ($service->isInUse()) {
            throw ValidationException::withMessages([
                'service' => __('No se puede eliminar: el servicio está en uso. Solo puede desactivarse.'),
            ]);
        }

        $service->delete();
    }
}
