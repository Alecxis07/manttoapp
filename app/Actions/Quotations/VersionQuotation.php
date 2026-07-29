<?php

namespace App\Actions\Quotations;

use App\DTOs\QuotationData;
use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Models\QuotationStatusHistory;
use App\Models\User;
use App\Services\TotalsCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VersionQuotation
{
    public function __construct(
        private CreateQuotation $createQuotation,
        private TotalsCalculator $totalsCalculator,
    ) {}

    public function handle(Quotation $quotation, ?QuotationData $data, User $actor): Quotation
    {
        if ($quotation->status === QuotationStatus::Draft) {
            throw ValidationException::withMessages([
                'status' => __('Use la edición normal para un borrador; el versionado aplica a cotizaciones enviadas.'),
            ]);
        }

        if (! in_array($quotation->status, [QuotationStatus::Sent, QuotationStatus::Rejected, QuotationStatus::Expired], true)) {
            throw ValidationException::withMessages([
                'status' => __('No se puede versionar una cotización en estado :status.', [
                    'status' => $quotation->status->label(),
                ]),
            ]);
        }

        return DB::transaction(function () use ($quotation, $data, $actor): Quotation {
            $quotation->loadMissing('items');

            $items = $data?->items ?? $quotation->items->map(fn ($item): array => [
                'item_type' => $item->item_type->value,
                'service_catalog_id' => $item->service_catalog_id,
                'part_catalog_id' => $item->part_catalog_id,
                'code' => $item->code,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount' => $item->discount,
                'notes' => $item->notes,
            ])->all();

            $payload = new QuotationData(
                customerId: $data?->customerId ?? $quotation->customer_id,
                vehicleId: $data?->vehicleId ?? $quotation->vehicle_id,
                validUntil: $data?->validUntil ?? ($quotation->valid_until?->toDateString()),
                commercialTerms: $data?->commercialTerms ?? $quotation->commercial_terms,
                items: $items,
            );

            $preparedItems = $this->createQuotation->prepareItems($payload->items);
            $this->createQuotation->assertDiscountAllowed($preparedItems, $actor);

            $totals = $this->totalsCalculator->calculate($preparedItems);
            $nextVersion = (int) Quotation::query()->where('folio', $quotation->folio)->max('version') + 1;

            $version = Quotation::query()->create([
                'folio' => $quotation->folio,
                'version' => $nextVersion,
                'parent_quotation_id' => $quotation->id,
                'customer_id' => $payload->customerId,
                'vehicle_id' => $payload->vehicleId,
                'status' => QuotationStatus::Draft,
                'valid_until' => $payload->validUntil,
                'commercial_terms' => $payload->commercialTerms,
                'subtotal' => $totals['subtotal'],
                'discount_total' => $totals['discount_total'],
                'tax_total' => $totals['tax_total'],
                'total' => $totals['total'],
                'tax_rate' => $totals['tax_rate'],
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $this->createQuotation->syncItems($version, $preparedItems);

            QuotationStatusHistory::query()->create([
                'quotation_id' => $version->id,
                'from_status' => null,
                'to_status' => QuotationStatus::Draft,
                'user_id' => $actor->id,
                'notes' => sprintf('Nueva versión a partir de v%d', $quotation->version),
                'created_at' => now(),
            ]);

            return $version->fresh(['items', 'customer', 'vehicle']);
        });
    }
}
