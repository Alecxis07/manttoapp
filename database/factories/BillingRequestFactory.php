<?php

namespace Database\Factories;

use App\Enums\BillingRequestStatus;
use App\Enums\QuotationItemType;
use App\Models\BillingRequest;
use App\Models\BillingRequestItem;
use App\Models\Customer;
use App\Models\CustomerFiscalProfile;
use App\Models\Quotation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillingRequest>
 */
class BillingRequestFactory extends Factory
{
    protected $model = BillingRequest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $customer = Customer::factory();

        return [
            'folio' => 'FAC-'.date('Y').'-'.str_pad((string) fake()->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'customer_id' => $customer,
            'vehicle_id' => null,
            'maintenance_order_id' => null,
            'quotation_id' => Quotation::factory(),
            'customer_fiscal_profile_id' => null,
            'fiscal_profile_snapshot' => [
                'legal_name' => fake()->company(),
                'rfc' => strtoupper(fake()->bothify('???######???')),
                'tax_regime_code' => '601',
                'cfdi_use_code' => 'G03',
                'postal_code' => fake()->numerify('#####'),
                'email' => fake()->safeEmail(),
                'snapshotted_at' => now()->toIso8601String(),
            ],
            'status' => BillingRequestStatus::Draft,
            'payment_method_code' => 'PUE',
            'payment_form_code' => '03',
            'currency' => 'MXN',
            'subtotal' => '1000.00',
            'discount_total' => '0.00',
            'tax_total' => '160.00',
            'total' => '1160.00',
            'tax_rate' => '16.00',
            'invoice_reference' => null,
            'notes' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (BillingRequest $billingRequest): void {
            if ($billingRequest->quotation_id && $billingRequest->customer_id === null) {
                $quotation = Quotation::query()->find($billingRequest->quotation_id);
                if ($quotation !== null) {
                    $billingRequest->customer_id = $quotation->customer_id;
                    $billingRequest->vehicle_id = $quotation->vehicle_id;
                }
            }
        })->afterCreating(function (BillingRequest $billingRequest): void {
            if ($billingRequest->quotation_id) {
                $quotation = Quotation::query()->find($billingRequest->quotation_id);
                if ($quotation !== null && (int) $billingRequest->customer_id !== (int) $quotation->customer_id) {
                    $billingRequest->forceFill([
                        'customer_id' => $quotation->customer_id,
                        'vehicle_id' => $quotation->vehicle_id,
                    ])->saveQuietly();
                }
            }

            if ($billingRequest->customer_fiscal_profile_id === null) {
                $profile = CustomerFiscalProfile::query()
                    ->where('customer_id', $billingRequest->customer_id)
                    ->where('is_default', true)
                    ->first();

                if ($profile !== null) {
                    $billingRequest->forceFill(['customer_fiscal_profile_id' => $profile->id])->saveQuietly();
                }
            }
        });
    }

    public function incompleteFiscal(): static
    {
        return $this->state(fn (array $attributes): array => [
            'fiscal_profile_snapshot' => [
                'legal_name' => null,
                'rfc' => null,
                'tax_regime_code' => null,
                'cfdi_use_code' => null,
                'postal_code' => null,
                'email' => null,
                'snapshotted_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => BillingRequestStatus::Approved,
        ]);
    }

    public function processed(string $reference = 'FAC-REF-001'): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => BillingRequestStatus::Processed,
            'invoice_reference' => $reference,
            'processed_at' => now(),
        ]);
    }

    public function withItem(string $unitPrice = '100.00'): static
    {
        return $this->afterCreating(function (BillingRequest $billingRequest) use ($unitPrice): void {
            BillingRequestItem::query()->create([
                'billing_request_id' => $billingRequest->id,
                'item_type' => QuotationItemType::Service,
                'code' => 'SRV-001',
                'description' => 'Servicio de prueba',
                'quantity' => '1.00',
                'unit_price' => $unitPrice,
                'discount' => '0.00',
                'line_total' => number_format((float) $unitPrice, 2, '.', ''),
                'sort_order' => 0,
            ]);

            $billingRequest->recalculateTotals();
        });
    }
}
