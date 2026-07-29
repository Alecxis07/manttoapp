<?php

namespace App\Actions\Quotations;

use App\DTOs\QuotationData;
use App\Models\Quotation;
use App\Models\User;
use App\Services\TotalsCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateQuotation
{
    public function __construct(
        private CreateQuotation $createQuotation,
        private TotalsCalculator $totalsCalculator,
    ) {}

    public function handle(Quotation $quotation, QuotationData $data, User $actor): Quotation
    {
        if (! $quotation->isEditable()) {
            throw ValidationException::withMessages([
                'status' => __('La cotización enviada es inmutable; genere una nueva versión (RN-COT-001).'),
            ]);
        }

        return DB::transaction(function () use ($quotation, $data, $actor): Quotation {
            $preparedItems = $this->createQuotation->prepareItems($data->items);
            $this->createQuotation->assertDiscountAllowed($preparedItems, $actor);

            $totals = $this->totalsCalculator->calculate(
                $preparedItems,
                $quotation->tax_rate !== null ? (string) $quotation->tax_rate : null
            );

            $quotation->update([
                'customer_id' => $data->customerId,
                'vehicle_id' => $data->vehicleId,
                'valid_until' => $data->validUntil,
                'commercial_terms' => $data->commercialTerms,
                'subtotal' => $totals['subtotal'],
                'discount_total' => $totals['discount_total'],
                'tax_total' => $totals['tax_total'],
                'total' => $totals['total'],
                'tax_rate' => $totals['tax_rate'],
                'updated_by' => $actor->id,
            ]);

            $this->createQuotation->syncItems($quotation, $preparedItems);

            return $quotation->fresh(['items', 'customer', 'vehicle']);
        });
    }
}
