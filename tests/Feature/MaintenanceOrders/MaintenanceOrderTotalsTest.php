<?php

namespace Tests\Feature\MaintenanceOrders;

use App\Enums\MaintenanceOrderStatus;
use App\Models\MaintenanceOrder;
use App\Models\ServiceCatalog;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\TotalsCalculator;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceOrderTotalsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_server_recalculates_totals_ignoring_client_values(): void
    {
        Setting::query()->create([
            'key' => TotalsCalculator::IVA_SETTING_KEY,
            'value' => '16.00',
            'type' => 'decimal',
            'group' => 'tax',
        ]);

        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $order = MaintenanceOrder::factory()->create([
            'tax_rate' => '16.00',
            'subtotal' => '0.00',
            'total' => '9999.00',
        ]);
        $service = ServiceCatalog::factory()->create(['base_price' => 100]);

        $this->actingAs($admin)->post(route('maintenance-orders.items.store', $order), [
            'service_catalog_id' => $service->id,
            'description' => $service->description,
            'quantity' => 2,
            'unit_price' => 100,
            'discount' => 20,
            // Manipulated client totals — must be ignored.
            'subtotal' => 1,
            'tax_total' => 1,
            'total' => 1,
        ])->assertRedirect();

        $order->refresh();

        // TotalsCalculator: subtotal = Σ(qty×price)=200; discount=20; taxable=180; IVA 16%=28.80; total=208.80
        $this->assertSame('200.00', (string) $order->subtotal);
        $this->assertSame('20.00', (string) $order->discount_total);
        $this->assertSame('28.80', (string) $order->tax_total);
        $this->assertSame('208.80', (string) $order->total);
        $this->assertSame('16.00', (string) $order->tax_rate);
    }

    public function test_completed_requires_items_diagnosis_and_assignee(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $order = MaintenanceOrder::factory()
            ->status(MaintenanceOrderStatus::InProgress)
            ->create([
                'diagnosis' => null,
                'assigned_user_id' => null,
            ]);

        $this->actingAs($admin)
            ->post(route('maintenance-orders.transition', $order), [
                'status' => MaintenanceOrderStatus::Completed->value,
            ])
            ->assertSessionHasErrors(['diagnosis', 'items', 'assigned_user_id']);
    }

    public function test_folio_generator_format_on_create(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $vehicle = Vehicle::factory()->create();

        $this->actingAs($admin)->post(route('maintenance-orders.store'), [
            'customer_id' => $vehicle->customer_id,
            'vehicle_id' => $vehicle->id,
            'type' => 'preventive',
            'reason' => 'Mantenimiento programado',
            'mileage' => 1000,
            'received_at' => now()->toDateTimeString(),
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('maintenance-orders.store'), [
            'customer_id' => $vehicle->customer_id,
            'vehicle_id' => $vehicle->id,
            'type' => 'corrective',
            'reason' => 'Segunda orden',
            'mileage' => 1001,
            'received_at' => now()->toDateTimeString(),
        ])->assertRedirect();

        $folios = MaintenanceOrder::query()->orderBy('id')->pluck('folio')->all();

        $this->assertCount(2, $folios);
        $this->assertMatchesRegularExpression('/^ORD-\d{4}-\d{5}$/', $folios[0]);
        $this->assertMatchesRegularExpression('/^ORD-\d{4}-\d{5}$/', $folios[1]);
        $this->assertNotSame($folios[0], $folios[1]);
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
