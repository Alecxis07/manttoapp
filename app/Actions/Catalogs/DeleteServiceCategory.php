<?php

namespace App\Actions\Catalogs;

use App\Models\ServiceCategory;
use Illuminate\Validation\ValidationException;

class DeleteServiceCategory
{
    /**
     * RN-CAT-001: physically delete only when not in use; otherwise reject.
     */
    public function handle(ServiceCategory $category): void
    {
        if ($category->isInUse()) {
            throw ValidationException::withMessages([
                'category' => __('No se puede eliminar: la categoría está en uso. Solo puede desactivarse.'),
            ]);
        }

        $category->delete();
    }
}
