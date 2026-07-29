<?php

namespace App\Actions\Maintenance;

use App\Models\MaintenanceOrder;
use App\Models\MaintenanceOrderItem;
use App\Models\ServiceCatalog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddOrderItem
{
    public function handle(
        MaintenanceOrder $order,
        User $actor,
        ?int $serviceCatalogId,
        string $description,
        float|string $quantity,
        float|string $unitPrice,
        float|string $discount = 0,
        ?string $notes = null,
        ?string $code = null,
    ): MaintenanceOrderItem {
        if (! $order->isEditable()) {
            throw ValidationException::withMessages([
                'order' => __('No se pueden modificar partidas de una orden terminada o cancelada.'),
            ]);
        }

        $catalog = null;

        if ($serviceCatalogId !== null) {
            $catalog = ServiceCatalog::query()->findOrFail($serviceCatalogId);

            if (! $catalog->is_active) {
                throw ValidationException::withMessages([
                    'service_catalog_id' => __('El servicio del catálogo no está activo.'),
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

        return DB::transaction(function () use ($order, $actor, $catalog, $code, $description, $quantity, $unitPrice, $discount, $lineTotal, $notes, $serviceCatalogId): MaintenanceOrderItem {
            $item = $order->items()->create([
                'service_catalog_id' => $serviceCatalogId,
                'code' => $code ?? $catalog?->code,
                'description' => $description,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount' => $discount,
                'line_total' => $lineTotal,
                'notes' => $notes,
                'sort_order' => (int) $order->items()->max('sort_order') + 1,
            ]);

            $order->forceFill(['updated_by' => $actor->id])->save();
            $order->recalculateTotals();

            return $item->fresh(['serviceCatalog']);
        });
    }

    private function money(float|int|string $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
