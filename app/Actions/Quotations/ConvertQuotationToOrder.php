<?php

namespace App\Actions\Quotations;

use App\Enums\MaintenanceOrderStatus;
use App\Enums\MaintenanceOrderType;
use App\Enums\QuotationItemType;
use App\Enums\QuotationStatus;
use App\Models\MaintenanceOrder;
use App\Models\MaintenanceOrderItem;
use App\Models\MaintenancePart;
use App\Models\MaintenanceStatusHistory;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use App\Support\FolioGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ConvertQuotationToOrder
{
    public function __construct(
        private FolioGenerator $folioGenerator,
    ) {}

    public function handle(Quotation $quotation, User $actor, ?MaintenanceOrderType $type = null): MaintenanceOrder
    {
        if ($quotation->status !== QuotationStatus::Accepted) {
            throw ValidationException::withMessages([
                'status' => __('Solo una cotización aceptada puede convertirse en orden.'),
            ]);
        }

        if ($quotation->maintenance_order_id !== null) {
            throw ValidationException::withMessages([
                'maintenance_order_id' => __('Esta cotización ya fue convertida a una orden.'),
            ]);
        }

        if (! Schema::hasTable('maintenance_orders')) {
            throw new RuntimeException('La tabla maintenance_orders aún no está disponible (Fase 5).');
        }

        return DB::transaction(function () use ($quotation, $actor, $type): MaintenanceOrder {
            $quotation->loadMissing(['items', 'vehicle']);

            $order = MaintenanceOrder::query()->create([
                'folio' => $this->folioGenerator->generate('maintenance_order'),
                'customer_id' => $quotation->customer_id,
                'vehicle_id' => $quotation->vehicle_id,
                'quotation_id' => $quotation->id,
                'type' => $type ?? MaintenanceOrderType::Corrective,
                'status' => MaintenanceOrderStatus::Received,
                'received_at' => now(),
                'mileage' => (int) ($quotation->vehicle?->current_mileage ?? 0),
                'reason' => sprintf('Convertida desde cotización %s v%d', $quotation->folio, $quotation->version),
                'subtotal' => $quotation->subtotal,
                'discount_total' => $quotation->discount_total,
                'tax_total' => $quotation->tax_total,
                'total' => $quotation->total,
                'tax_rate' => $quotation->tax_rate,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            foreach ($quotation->items as $index => $item) {
                $this->copyItemToOrder($order, $item, $index);
            }

            if (Schema::hasTable('maintenance_status_history')) {
                MaintenanceStatusHistory::query()->create([
                    'maintenance_order_id' => $order->id,
                    'from_status' => null,
                    'to_status' => MaintenanceOrderStatus::Received,
                    'user_id' => $actor->id,
                    'notes' => sprintf('Creada desde cotización %s', $quotation->folio),
                    'created_at' => now(),
                ]);
            }

            $quotation->update([
                'maintenance_order_id' => $order->id,
                'updated_by' => $actor->id,
            ]);

            return $order->fresh(['items', 'parts']);
        });
    }

    private function copyItemToOrder(MaintenanceOrder $order, QuotationItem $item, int $sortOrder): void
    {
        $payload = [
            'maintenance_order_id' => $order->id,
            'code' => $item->code,
            'description' => $item->description,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'discount' => $item->discount,
            'line_total' => $item->line_total,
            'notes' => $item->notes,
            'sort_order' => $sortOrder,
        ];

        if ($item->item_type === QuotationItemType::Service) {
            MaintenanceOrderItem::query()->create([
                ...$payload,
                'service_catalog_id' => $item->service_catalog_id,
            ]);

            return;
        }

        MaintenancePart::query()->create([
            ...$payload,
            'part_catalog_id' => $item->part_catalog_id,
        ]);
    }
}
