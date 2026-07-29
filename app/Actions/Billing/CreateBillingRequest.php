<?php

namespace App\Actions\Billing;

use App\DTOs\BillingRequestData;
use App\Enums\BillingRequestStatus;
use App\Enums\QuotationItemType;
use App\Models\BillingRequest;
use App\Models\BillingRequestItem;
use App\Models\BillingRequestStatusHistory;
use App\Models\Customer;
use App\Models\CustomerFiscalProfile;
use App\Models\MaintenanceOrder;
use App\Models\Quotation;
use App\Models\User;
use App\Services\TotalsCalculator;
use App\Support\FolioGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateBillingRequest
{
    public function __construct(
        private FolioGenerator $folioGenerator,
        private TotalsCalculator $totalsCalculator,
    ) {}

    public function handle(BillingRequestData $data, User $actor): BillingRequest
    {
        if ($data->maintenanceOrderId === null && $data->quotationId === null) {
            throw ValidationException::withMessages([
                'origin' => __('La solicitud debe originarse desde una orden o una cotización (RN-FAC-001).'),
            ]);
        }

        if ($data->maintenanceOrderId !== null && $data->quotationId !== null) {
            throw ValidationException::withMessages([
                'origin' => __('Indique solo un origen: orden o cotización.'),
            ]);
        }

        return DB::transaction(function () use ($data, $actor): BillingRequest {
            [$customer, $vehicleId, $preparedItems, $taxRate] = $this->resolveOrigin($data);

            $profile = $this->resolveFiscalProfile($customer, $data->customerFiscalProfileId);
            $snapshot = $this->snapshotFiscalProfile($profile);

            $totals = $this->totalsCalculator->calculate($preparedItems, $taxRate);

            $billingRequest = BillingRequest::query()->create([
                'folio' => $this->folioGenerator->generate('billing_request'),
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicleId,
                'maintenance_order_id' => $data->maintenanceOrderId,
                'quotation_id' => $data->quotationId,
                'customer_fiscal_profile_id' => $profile?->id,
                'fiscal_profile_snapshot' => $snapshot,
                'status' => BillingRequestStatus::Draft,
                'payment_method_code' => $data->paymentMethodCode,
                'payment_form_code' => $data->paymentFormCode,
                'currency' => 'MXN',
                'subtotal' => $totals['subtotal'],
                'discount_total' => $totals['discount_total'],
                'tax_total' => $totals['tax_total'],
                'total' => $totals['total'],
                'tax_rate' => $totals['tax_rate'],
                'notes' => $data->notes,
                'requested_by' => $actor->id,
            ]);

            foreach ($preparedItems as $item) {
                BillingRequestItem::query()->create([
                    'billing_request_id' => $billingRequest->id,
                    ...$item,
                ]);
            }

            BillingRequestStatusHistory::query()->create([
                'billing_request_id' => $billingRequest->id,
                'from_status' => null,
                'to_status' => BillingRequestStatus::Draft,
                'user_id' => $actor->id,
                'notes' => 'Solicitud de facturación creada',
                'created_at' => now(),
            ]);

            return $billingRequest->fresh(['items', 'customer', 'vehicle']);
        });
    }

    /**
     * @return array{0: Customer, 1: int|null, 2: list<array<string, mixed>>, 3: string}
     */
    private function resolveOrigin(BillingRequestData $data): array
    {
        if ($data->maintenanceOrderId !== null) {
            $order = MaintenanceOrder::query()
                ->with(['items', 'parts', 'customer'])
                ->findOrFail($data->maintenanceOrderId);

            $prepared = [];
            $sort = 0;

            foreach ($order->items as $item) {
                $prepared[] = [
                    'item_type' => QuotationItemType::Service,
                    'service_catalog_id' => $item->service_catalog_id,
                    'part_catalog_id' => null,
                    'code' => $item->code,
                    'description' => $item->description,
                    'quantity' => number_format((float) $item->quantity, 2, '.', ''),
                    'unit_price' => number_format((float) $item->unit_price, 2, '.', ''),
                    'discount' => number_format((float) $item->discount, 2, '.', ''),
                    'line_total' => number_format((float) $item->line_total, 2, '.', ''),
                    'notes' => $item->notes,
                    'sort_order' => $sort++,
                ];
            }

            foreach ($order->parts as $part) {
                $prepared[] = [
                    'item_type' => QuotationItemType::Part,
                    'service_catalog_id' => null,
                    'part_catalog_id' => $part->part_catalog_id,
                    'code' => $part->code,
                    'description' => $part->description,
                    'quantity' => number_format((float) $part->quantity, 2, '.', ''),
                    'unit_price' => number_format((float) $part->unit_price, 2, '.', ''),
                    'discount' => number_format((float) $part->discount, 2, '.', ''),
                    'line_total' => number_format((float) $part->line_total, 2, '.', ''),
                    'notes' => $part->notes,
                    'sort_order' => $sort++,
                ];
            }

            if ($prepared === []) {
                throw ValidationException::withMessages([
                    'maintenance_order_id' => __('La orden no tiene conceptos para facturar.'),
                ]);
            }

            return [
                $order->customer,
                $order->vehicle_id,
                $prepared,
                (string) $order->tax_rate,
            ];
        }

        $quotation = Quotation::query()
            ->with(['items', 'customer'])
            ->findOrFail($data->quotationId);

        $prepared = [];

        foreach ($quotation->items as $index => $item) {
            $prepared[] = [
                'item_type' => $item->item_type,
                'service_catalog_id' => $item->service_catalog_id,
                'part_catalog_id' => $item->part_catalog_id,
                'code' => $item->code,
                'description' => $item->description,
                'quantity' => number_format((float) $item->quantity, 2, '.', ''),
                'unit_price' => number_format((float) $item->unit_price, 2, '.', ''),
                'discount' => number_format((float) $item->discount, 2, '.', ''),
                'line_total' => number_format((float) $item->line_total, 2, '.', ''),
                'notes' => $item->notes,
                'sort_order' => $index,
            ];
        }

        if ($prepared === []) {
            throw ValidationException::withMessages([
                'quotation_id' => __('La cotización no tiene conceptos para facturar.'),
            ]);
        }

        return [
            $quotation->customer,
            $quotation->vehicle_id,
            $prepared,
            (string) $quotation->tax_rate,
        ];
    }

    private function resolveFiscalProfile(Customer $customer, ?int $profileId): ?CustomerFiscalProfile
    {
        if ($profileId !== null) {
            $profile = CustomerFiscalProfile::query()
                ->where('customer_id', $customer->id)
                ->whereKey($profileId)
                ->first();

            if ($profile === null) {
                throw ValidationException::withMessages([
                    'customer_fiscal_profile_id' => __('El perfil fiscal no pertenece al cliente.'),
                ]);
            }

            return $profile;
        }

        return CustomerFiscalProfile::query()
            ->where('customer_id', $customer->id)
            ->where('is_default', true)
            ->first()
            ?? CustomerFiscalProfile::query()
                ->where('customer_id', $customer->id)
                ->orderBy('id')
                ->first();
    }

    /**
     * @return array{
     *     legal_name: string|null,
     *     rfc: string|null,
     *     tax_regime_code: string|null,
     *     cfdi_use_code: string|null,
     *     postal_code: string|null,
     *     email: string|null,
     *     snapshotted_at: string
     * }
     */
    private function snapshotFiscalProfile(?CustomerFiscalProfile $profile): array
    {
        return [
            'legal_name' => $profile?->legal_name,
            'rfc' => $profile?->rfc,
            'tax_regime_code' => $profile?->tax_regime_code,
            'cfdi_use_code' => $profile?->cfdi_use_code,
            'postal_code' => $profile?->postal_code,
            'email' => $profile?->email,
            'snapshotted_at' => now()->toIso8601String(),
        ];
    }
}
