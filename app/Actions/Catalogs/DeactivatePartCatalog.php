<?php

namespace App\Actions\Catalogs;

use App\Models\PartCatalog;

class DeactivatePartCatalog
{
    public function handle(PartCatalog $part): PartCatalog
    {
        $part->update([
            'is_active' => false,
        ]);

        return $part->fresh(['category']);
    }
}
