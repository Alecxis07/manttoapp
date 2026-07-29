<?php

namespace Database\Factories;

use App\Enums\QuotationItemType;
use App\Enums\QuotationStatus;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\ServiceCatalog;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quotation>
 */
class QuotationFactory extends Factory
{
    protected $model = Quotation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'folio' => 'COT-'.date('Y').'-'.str_pad((string) fake()->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'version' => 1,
            'customer_id' => Customer::factory(),
            'vehicle_id' => fn (array $attributes) => Vehicle::factory()->create([
                'customer_id' => $attributes['customer_id'],
            ])->id,
            'status' => QuotationStatus::Draft,
            'valid_until' => now()->addDays(15)->toDateString(),
            'subtotal' => '1000.00',
            'discount_total' => '0.00',
            'tax_total' => '160.00',
            'total' => '1160.00',
            'tax_rate' => '16.00',
            'commercial_terms' => 'Vigencia 15 días.',
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => QuotationStatus::Sent,
            'issued_at' => now(),
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => QuotationStatus::Accepted,
            'issued_at' => now()->subDay(),
            'accepted_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => QuotationStatus::Expired,
            'issued_at' => now()->subDays(30),
            'valid_until' => now()->subDay()->toDateString(),
        ]);
    }

    public function withServiceItem(?ServiceCatalog $service = null): static
    {
        return $this->afterCreating(function (Quotation $quotation) use ($service): void {
            $service ??= ServiceCatalog::factory()->create();

            QuotationItem::query()->create([
                'quotation_id' => $quotation->id,
                'item_type' => QuotationItemType::Service,
                'service_catalog_id' => $service->id,
                'code' => $service->code,
                'description' => $service->description,
                'quantity' => '1.00',
                'unit_price' => $service->base_price,
                'discount' => '0.00',
                'line_total' => number_format((float) $service->base_price, 2, '.', ''),
                'sort_order' => 0,
            ]);

            $quotation->recalculateTotals();
        });
    }
}
