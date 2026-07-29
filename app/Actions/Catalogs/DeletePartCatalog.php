<?php

namespace App\Actions\Catalogs;

use App\Models\PartCatalog;
use Illuminate\Validation\ValidationException;

class DeletePartCatalog
{
    /**
     * RN-CAT-001: physically delete only when not in use; otherwise reject.
     */
    public function handle(PartCatalog $part): void
    {
        if ($part->isInUse()) {
            throw ValidationException::withMessages([
                'part' => __('No se puede eliminar: el concepto está en uso. Solo puede desactivarse.'),
            ]);
        }

        $part->delete();
    }
}
