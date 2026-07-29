<?php

namespace Tests\Feature\BillingRequests;

use App\Enums\BillingRequestStatus;
use App\Enums\QuotationItemType;
use App\Models\BillingRequest;
use App\Models\Customer;
use App\Models\CustomerFiscalProfile;
use App\Models\MaintenanceOrder;
use App\Models\MaintenanceOrderItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\ServiceCatalog;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingRequestManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_create_billing_request_from_order(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $order = $this->createOrderWithItemAndFiscal();

        $this->actingAs($admin)
            ->post(route('billing-requests.store'), [
                'maintenance_order_id' => $order->id,
                'payment_method_code' => 'PUE',
                'payment_form_code' => '03',
            ])
            ->assertRedirect();

        $billing = BillingRequest::query()->latest('id')->firstOrFail();

        $this->assertSame($order->id, $billing->maintenance_order_id);
        $this->assertNull($billing->quotation_id);
        $this->assertSame(BillingRequestStatus::Draft, $billing->status);
        $this->assertStringStartsWith('FAC-', $billing->folio);
        $this->assertGreaterThan(0, $billing->items()->count());
    }

    public function test_admin_can_create_billing_request_from_quotation(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $quotation = $this->createQuotationWithItemAndFiscal();

        $this->actingAs($admin)
            ->get(route('billing-requests.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->post(route('billing-requests.store'), [
                'quotation_id' => $quotation->id,
                'payment_method_code' => 'PUE',
                'payment_form_code' => '03',
            ])
            ->assertRedirect();

        $billing = BillingRequest::query()->latest('id')->firstOrFail();
        $this->assertSame($quotation->id, $billing->quotation_id);
        $this->assertSame((string) $quotation->total, (string) $billing->total);
    }

    public function test_technician_cannot_create_billing_request(): void
    {
        $tech = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_TECNICO);
        $quotation = $this->createQuotationWithItemAndFiscal();

        $this->actingAs($tech)
            ->post(route('billing-requests.store'), [
                'quotation_id' => $quotation->id,
                'payment_method_code' => 'PUE',
                'payment_form_code' => '03',
            ])
            ->assertForbidden();
    }

    public function test_full_status_flow_to_processed(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $quotation = $this->createQuotationWithItemAndFiscal();

        $this->actingAs($admin)->post(route('billing-requests.store'), [
            'quotation_id' => $quotation->id,
            'payment_method_code' => 'PUE',
            'payment_form_code' => '03',
        ]);

        $billing = BillingRequest::query()->latest('id')->firstOrFail();

        $this->actingAs($admin)->post(route('billing-requests.submit', $billing))->assertRedirect();
        $this->assertSame(BillingRequestStatus::PendingReview, $billing->fresh()->status);

        $this->actingAs($admin)->post(route('billing-requests.transition', $billing), [
            'status' => BillingRequestStatus::Incomplete->value,
            'notes' => 'Falta CP',
        ])->assertRedirect();
        $this->assertSame(BillingRequestStatus::Incomplete, $billing->fresh()->status);

        $this->actingAs($admin)->post(route('billing-requests.submit', $billing))->assertRedirect();
        $this->actingAs($admin)->post(route('billing-requests.transition', $billing), [
            'status' => BillingRequestStatus::Approved->value,
        ])->assertRedirect();
        $this->actingAs($admin)->post(route('billing-requests.process', $billing), [
            'invoice_reference' => 'A-12345',
        ])->assertRedirect(route('billing-requests.show', $billing));

        $billing->refresh();
        $this->assertSame(BillingRequestStatus::Processed, $billing->status);
        $this->assertSame('A-12345', $billing->invoice_reference);
        $this->assertNotNull($billing->processed_at);
    }

    public function test_requires_origin_document(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);

        $this->actingAs($admin)
            ->post(route('billing-requests.store'), [
                'payment_method_code' => 'PUE',
                'payment_form_code' => '03',
            ])
            ->assertSessionHasErrors('origin');
    }

    private function createQuotationWithItemAndFiscal(): Quotation
    {
        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);
        CustomerFiscalProfile::factory()->create([
            'customer_id' => $customer->id,
            'is_default' => true,
        ]);
        $service = ServiceCatalog::factory()->create(['base_price' => '200.00']);

        $quotation = Quotation::factory()->create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'subtotal' => '200.00',
            'discount_total' => '0.00',
            'tax_total' => '32.00',
            'total' => '232.00',
            'tax_rate' => '16.00',
        ]);

        QuotationItem::query()->create([
            'quotation_id' => $quotation->id,
            'item_type' => QuotationItemType::Service,
            'service_catalog_id' => $service->id,
            'code' => $service->code,
            'description' => $service->description,
            'quantity' => '1.00',
            'unit_price' => '200.00',
            'discount' => '0.00',
            'line_total' => '200.00',
            'sort_order' => 0,
        ]);

        return $quotation->fresh('items');
    }

    private function createOrderWithItemAndFiscal(): MaintenanceOrder
    {
        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);
        CustomerFiscalProfile::factory()->create([
            'customer_id' => $customer->id,
            'is_default' => true,
        ]);
        $service = ServiceCatalog::factory()->create(['base_price' => '150.00']);

        $order = MaintenanceOrder::factory()->forVehicle($vehicle)->create([
            'subtotal' => '150.00',
            'discount_total' => '0.00',
            'tax_total' => '24.00',
            'total' => '174.00',
            'tax_rate' => '16.00',
        ]);

        MaintenanceOrderItem::query()->create([
            'maintenance_order_id' => $order->id,
            'service_catalog_id' => $service->id,
            'code' => $service->code,
            'description' => $service->description,
            'quantity' => '1.00',
            'unit_price' => '150.00',
            'discount' => '0.00',
            'line_total' => '150.00',
            'sort_order' => 0,
        ]);

        return $order->fresh(['items', 'parts']);
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
