<?php

namespace Tests\Feature\BillingRequests;

use App\Enums\BillingRequestStatus;
use App\Enums\QuotationItemType;
use App\Models\BillingRequest;
use App\Models\BillingRequestItem;
use App\Models\Customer;
use App\Models\CustomerFiscalProfile;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\ServiceCatalog;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingRequestBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_fiscal_snapshot_is_immutable_when_customer_profile_changes_rn_fac_001(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        [$quotation, $profile] = $this->createQuotationWithFiscalProfile();

        $this->actingAs($admin)
            ->post(route('billing-requests.store'), [
                'quotation_id' => $quotation->id,
                'customer_fiscal_profile_id' => $profile->id,
                'payment_method_code' => 'PUE',
                'payment_form_code' => '03',
            ])
            ->assertRedirect();

        $billing = BillingRequest::query()->latest('id')->firstOrFail();
        $originalRfc = $billing->fiscal_profile_snapshot['rfc'];

        $profile->update(['rfc' => 'XAXX010101000', 'legal_name' => 'Empresa Nueva SA']);

        $billing->refresh();

        $this->assertSame($originalRfc, $billing->fiscal_profile_snapshot['rfc']);
        $this->assertNotSame('XAXX010101000', $billing->fiscal_profile_snapshot['rfc']);
        $this->assertNotSame('Empresa Nueva SA', $billing->fiscal_profile_snapshot['legal_name']);
    }

    public function test_incomplete_fiscal_data_cannot_submit_for_review_rn_fac_002(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        [$quotation] = $this->createQuotationWithFiscalProfile(withProfile: false);

        $this->actingAs($admin)
            ->post(route('billing-requests.store'), [
                'quotation_id' => $quotation->id,
                'payment_method_code' => 'PUE',
                'payment_form_code' => '03',
            ])
            ->assertRedirect();

        $billing = BillingRequest::query()->latest('id')->firstOrFail();

        $this->assertFalse($billing->hasCompleteFiscalData());

        $this->actingAs($admin)
            ->post(route('billing-requests.submit', $billing))
            ->assertSessionHasErrors('fiscal_profile_snapshot');

        $this->assertSame(BillingRequestStatus::Draft, $billing->fresh()->status);
    }

    public function test_processed_billing_request_is_immutable_rn_fac_003(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        [$quotation, $profile] = $this->createQuotationWithFiscalProfile();

        $this->actingAs($admin)->post(route('billing-requests.store'), [
            'quotation_id' => $quotation->id,
            'customer_fiscal_profile_id' => $profile->id,
            'payment_method_code' => 'PUE',
            'payment_form_code' => '03',
        ]);

        $billing = BillingRequest::query()->latest('id')->firstOrFail();

        $this->actingAs($admin)->post(route('billing-requests.submit', $billing))->assertRedirect();
        $this->actingAs($admin)->post(route('billing-requests.transition', $billing), [
            'status' => BillingRequestStatus::Approved->value,
        ])->assertRedirect();
        $this->actingAs($admin)->post(route('billing-requests.process', $billing), [
            'invoice_reference' => 'UUID-CFDI-123',
        ])->assertRedirect();

        $billing->refresh();
        $this->assertSame(BillingRequestStatus::Processed, $billing->status);
        $this->assertSame('UUID-CFDI-123', $billing->invoice_reference);
        $this->assertTrue($billing->isImmutable());

        $this->actingAs($admin)
            ->put(route('billing-requests.update', $billing), [
                'payment_method_code' => 'PPD',
                'payment_form_code' => '04',
            ])
            ->assertSessionHasErrors('status');

        $this->actingAs($admin)
            ->post(route('billing-requests.transition', $billing), [
                'status' => BillingRequestStatus::Cancelled->value,
            ])
            ->assertSessionHasErrors('status');

        $this->assertSame('PUE', $billing->fresh()->payment_method_code);
        $this->assertSame(BillingRequestStatus::Processed, $billing->fresh()->status);
    }

    public function test_amounts_match_included_concepts(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        [$quotation] = $this->createQuotationWithFiscalProfile();

        $this->actingAs($admin)->post(route('billing-requests.store'), [
            'quotation_id' => $quotation->id,
            'payment_method_code' => 'PUE',
            'payment_form_code' => '03',
        ]);

        $billing = BillingRequest::query()->latest('id')->firstOrFail();
        $billing->load('items');

        $itemsSubtotal = $billing->items->reduce(
            fn (string $carry, BillingRequestItem $item): string => bcadd(
                $carry,
                bcmul((string) $item->quantity, (string) $item->unit_price, 2),
                2
            ),
            '0.00'
        );
        $itemsDiscount = $billing->items->reduce(
            fn (string $carry, BillingRequestItem $item): string => bcadd($carry, (string) $item->discount, 2),
            '0.00'
        );
        $itemsLineTotal = $billing->items->reduce(
            fn (string $carry, BillingRequestItem $item): string => bcadd($carry, (string) $item->line_total, 2),
            '0.00'
        );

        $this->assertSame($itemsSubtotal, (string) $billing->subtotal);
        $this->assertSame($itemsDiscount, (string) $billing->discount_total);
        $taxable = bcsub($itemsSubtotal, $itemsDiscount, 2);
        $expectedTax = bcmul($taxable, bcdiv((string) $billing->tax_rate, '100', 6), 2);
        $expectedTotal = bcadd($taxable, $expectedTax, 2);

        $this->assertSame($expectedTax, (string) $billing->tax_total);
        $this->assertSame($expectedTotal, (string) $billing->total);
        $this->assertSame($taxable, $itemsLineTotal);
        $this->assertSame((string) $quotation->total, (string) $billing->total);
    }

    /**
     * @return array{0: Quotation, 1: CustomerFiscalProfile|null}
     */
    private function createQuotationWithFiscalProfile(bool $withProfile = true): array
    {
        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);
        $service = ServiceCatalog::factory()->create(['base_price' => '100.00']);

        $profile = null;

        if ($withProfile) {
            $profile = CustomerFiscalProfile::factory()->create([
                'customer_id' => $customer->id,
                'legal_name' => 'Cliente Fiscal SA de CV',
                'rfc' => 'CFI850101ABC',
                'tax_regime_code' => '601',
                'cfdi_use_code' => 'G03',
                'postal_code' => '06600',
                'is_default' => true,
            ]);
        }

        $quotation = Quotation::factory()->create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'subtotal' => '100.00',
            'discount_total' => '0.00',
            'tax_total' => '16.00',
            'total' => '116.00',
            'tax_rate' => '16.00',
        ]);

        QuotationItem::query()->create([
            'quotation_id' => $quotation->id,
            'item_type' => QuotationItemType::Service,
            'service_catalog_id' => $service->id,
            'code' => $service->code,
            'description' => $service->description,
            'quantity' => '1.00',
            'unit_price' => '100.00',
            'discount' => '0.00',
            'line_total' => '100.00',
            'sort_order' => 0,
        ]);

        return [$quotation->fresh('items'), $profile];
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
