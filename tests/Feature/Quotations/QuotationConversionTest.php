<?php

namespace Tests\Feature\Quotations;

use App\Enums\QuotationItemType;
use App\Enums\QuotationStatus;
use App\Models\Customer;
use App\Models\MaintenanceOrder;
use App\Models\PartCatalog;
use App\Models\Quotation;
use App\Models\ServiceCatalog;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationConversionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_accepted_quotation_converts_to_order_with_snapshot_integrity(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create([
            'customer_id' => $customer->id,
            'current_mileage' => 45000,
        ]);
        $service = ServiceCatalog::factory()->create(['base_price' => '800.00', 'code' => 'SRV-01']);
        $part = PartCatalog::factory()->create(['base_price' => '200.00', 'code' => 'PRT-01']);

        $this->actingAs($admin)->post(route('quotations.store'), [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'valid_until' => now()->addDays(7)->toDateString(),
            'items' => [
                [
                    'item_type' => QuotationItemType::Service->value,
                    'service_catalog_id' => $service->id,
                    'description' => $service->description,
                    'quantity' => 1,
                    'unit_price' => 800,
                    'discount' => 0,
                ],
                [
                    'item_type' => QuotationItemType::Part->value,
                    'part_catalog_id' => $part->id,
                    'description' => $part->description,
                    'quantity' => 2,
                    'unit_price' => 200,
                    'discount' => 50,
                ],
            ],
        ]);

        $quotation = Quotation::query()->latest('id')->firstOrFail();

        $this->actingAs($admin)->post(route('quotations.send', $quotation));
        $this->actingAs($admin)->post(route('quotations.accept', $quotation->fresh()));

        $response = $this->actingAs($admin)->post(route('quotations.convert', $quotation->fresh()));

        $quotation->refresh();
        $order = MaintenanceOrder::query()->findOrFail($quotation->maintenance_order_id);

        $response->assertRedirect(route('maintenance-orders.show', $order));

        $this->assertSame($quotation->id, $order->quotation_id);
        $this->assertSame($quotation->customer_id, $order->customer_id);
        $this->assertSame($quotation->vehicle_id, $order->vehicle_id);
        $this->assertSame((string) $quotation->subtotal, (string) $order->subtotal);
        $this->assertSame((string) $quotation->discount_total, (string) $order->discount_total);
        $this->assertSame((string) $quotation->tax_total, (string) $order->tax_total);
        $this->assertSame((string) $quotation->total, (string) $order->total);
        $this->assertSame((string) $quotation->tax_rate, (string) $order->tax_rate);
        $this->assertSame(1, $order->items()->count());
        $this->assertSame(1, $order->parts()->count());
        $this->assertSame('800.00', (string) $order->items()->first()->unit_price);
        $this->assertSame('200.00', (string) $order->parts()->first()->unit_price);
        $this->assertSame('50.00', (string) $order->parts()->first()->discount);
        $this->assertSame(QuotationStatus::Accepted, $quotation->status);
    }

    public function test_cannot_convert_twice(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $quotation = Quotation::factory()->accepted()->withServiceItem()->create();

        $this->actingAs($admin)->post(route('quotations.convert', $quotation))->assertRedirect();
        $this->actingAs($admin)
            ->post(route('quotations.convert', $quotation->fresh()))
            ->assertSessionHasErrors('maintenance_order_id');
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
