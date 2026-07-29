<?php

namespace App\Actions\Maintenance;

use App\Models\MaintenanceOrder;
use App\Models\MaintenancePart;
use App\Models\PartCatalog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddOrderPart
{
    public function handle(
        MaintenanceOrder $order,
        User $actor,
        ?int $partCatalogId,
        string $description,
        float|string $quantity,
        float|string $unitPrice,
        float|string $discount = 0,
        ?string $notes = null,
        ?string $code = null,
    ): MaintenancePart {
        if (! $order->isEditable()) {
            throw ValidationException::withMessages([
                'order' => __('No se pueden modificar refacciones de una orden terminada o cancelada.'),
            ]);
        }

        $catalog = null;

        if ($partCatalogId !== null) {
            $catalog = PartCatalog::query()->findOrFail($partCatalogId);

            if (! $catalog->is_active) {
                throw ValidationException::withMessages([
                    'part_catalog_id' => __('La refacción del catálogo no está activa.'),
                ]);
            }

            $code ??= $catalog->code;
            $description = $description !== '' ? $description : $catalog->description;
        }

        $quantity = $this->money($quantity);
        $unitPrice = $this->money($unitPrice);
        $discount = $this->money($discount);
        $lineGross = bcmul($quantity, $unitPrice, 2);
        $lineTotal = bcsub($lineGross, $discount, 2);

        if (bccomp($lineTotal, '0', 2) === -1) {
            throw ValidationException::withMessages([
                'discount' => __('El descuento no puede exceder el importe de la partida.'),
            ]);
        }

        return DB::transaction(function () use ($order, $actor, $catalog, $code, $description, $quantity, $unitPrice, $discount, $lineTotal, $notes, $partCatalogId): MaintenancePart {
            $part = $order->parts()->create([
                'part_catalog_id' => $partCatalogId,
                'code' => $code ?? $catalog?->code,
                'description' => $description,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount' => $discount,
                'line_total' => $lineTotal,
                'notes' => $notes,
                'sort_order' => (int) $order->parts()->max('sort_order') + 1,
            ]);

            $order->forceFill(['updated_by' => $actor->id])->save();
            $order->recalculateTotals();

            return $part->fresh(['partCatalog']);
        });
    }

    private function money(float|int|string $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
