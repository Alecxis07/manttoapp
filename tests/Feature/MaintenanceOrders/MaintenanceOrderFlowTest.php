<?php

namespace Tests\Feature\MaintenanceOrders;

use App\Enums\CustomerStatus;
use App\Enums\MaintenanceOrderStatus;
use App\Enums\MaintenanceOrderType;
use App\Models\Customer;
use App\Models\MaintenanceOrder;
use App\Models\ServiceCatalog;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MaintenanceOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_creates_order_with_ord_folio_and_initial_status(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $customer = Customer::factory()->create(['status' => CustomerStatus::Active]);
        $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($admin)->post(route('maintenance-orders.store'), [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'type' => MaintenanceOrderType::Corrective->value,
            'reason' => 'Falla en frenos',
            'mileage' => 120000,
            'received_at' => now()->toDateTimeString(),
            'assigned_user_id' => $admin->id,
        ]);

        $order = MaintenanceOrder::query()->first();

        $this->assertNotNull($order);
        $response->assertRedirect(route('maintenance-orders.show', $order));
        $this->assertMatchesRegularExpression('/^ORD-\d{4}-\d{5}$/', $order->folio);
        $this->assertSame(MaintenanceOrderStatus::Received, $order->status);
        $this->assertSame('16.00', (string) $order->tax_rate);
        $this->assertDatabaseHas('maintenance_status_history', [
            'maintenance_order_id' => $order->id,
            'to_status' => MaintenanceOrderStatus::Received->value,
        ]);
    }

    public function test_rejects_vehicle_not_belonging_to_customer(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $customer = Customer::factory()->create(['status' => CustomerStatus::Active]);
        $otherVehicle = Vehicle::factory()->create();

        $this->actingAs($admin)
            ->post(route('maintenance-orders.store'), [
                'customer_id' => $customer->id,
                'vehicle_id' => $otherVehicle->id,
                'type' => MaintenanceOrderType::Preventive->value,
                'reason' => 'Servicio',
                'mileage' => 10,
                'received_at' => now()->toDateTimeString(),
            ])
            ->assertSessionHasErrors('vehicle_id');
    }

    public function test_full_flow_reception_to_delivery(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $customer = Customer::factory()->create(['status' => CustomerStatus::Active]);
        $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);
        $service = ServiceCatalog::factory()->create(['base_price' => 1000]);

        $this->actingAs($admin)->post(route('maintenance-orders.store'), [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'type' => MaintenanceOrderType::Corrective->value,
            'reason' => 'Ruido en motor',
            'mileage' => 50000,
            'received_at' => now()->toDateTimeString(),
            'assigned_user_id' => $admin->id,
        ])->assertRedirect();

        $order = MaintenanceOrder::query()->firstOrFail();

        $this->actingAs($admin)->post(route('maintenance-orders.transition', $order), [
            'status' => MaintenanceOrderStatus::Diagnosing->value,
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('maintenance-orders.diagnose', $order), [
            'diagnosis' => 'Banda de distribución desgastada',
            'assigned_user_id' => $admin->id,
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('maintenance-orders.transition', $order), [
            'status' => MaintenanceOrderStatus::PendingApproval->value,
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('maintenance-orders.transition', $order), [
            'status' => MaintenanceOrderStatus::Approved->value,
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('maintenance-orders.items.store', $order), [
            'service_catalog_id' => $service->id,
            'description' => $service->description,
            'quantity' => 1,
            'unit_price' => 1000,
            'discount' => 0,
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('maintenance-orders.transition', $order), [
            'status' => MaintenanceOrderStatus::InProgress->value,
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('maintenance-orders.transition', $order), [
            'status' => MaintenanceOrderStatus::Completed->value,
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('maintenance-orders.transition', $order), [
            'status' => MaintenanceOrderStatus::Delivered->value,
        ])->assertRedirect();

        $order->refresh();

        $this->assertSame(MaintenanceOrderStatus::Delivered, $order->status);
        $this->assertNotNull($order->completed_at);
        $this->assertNotNull($order->delivered_at);
        $this->assertTrue($order->delivered_at->greaterThanOrEqualTo($order->completed_at));
    }

    public function test_in_progress_requires_diagnosis(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $order = MaintenanceOrder::factory()
            ->status(MaintenanceOrderStatus::Approved)
            ->create(['diagnosis' => null]);

        $this->actingAs($admin)
            ->post(route('maintenance-orders.transition', $order), [
                'status' => MaintenanceOrderStatus::InProgress->value,
            ])
            ->assertSessionHasErrors('diagnosis');
    }

    public function test_invalid_transition_is_rejected(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $order = MaintenanceOrder::factory()->create();

        $this->actingAs($admin)
            ->post(route('maintenance-orders.transition', $order), [
                'status' => MaintenanceOrderStatus::Completed->value,
            ])
            ->assertSessionHasErrors('status');
    }

    public function test_cancel_requires_reason_and_admin_only(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $administrativo = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO);
        $order = MaintenanceOrder::factory()->create();

        $this->actingAs($administrativo)
            ->post(route('maintenance-orders.transition', $order), [
                'status' => MaintenanceOrderStatus::Cancelled->value,
                'cancellation_reason' => 'Cliente canceló',
            ])
            ->assertSessionHasErrors('status');

        $this->actingAs($admin)
            ->post(route('maintenance-orders.transition', $order), [
                'status' => MaintenanceOrderStatus::Cancelled->value,
            ])
            ->assertSessionHasErrors('cancellation_reason');

        $this->actingAs($admin)
            ->post(route('maintenance-orders.transition', $order), [
                'status' => MaintenanceOrderStatus::Cancelled->value,
                'cancellation_reason' => 'Cliente canceló el servicio',
            ])
            ->assertRedirect();

        $this->assertSame(MaintenanceOrderStatus::Cancelled, $order->fresh()->status);
    }

    public function test_reopen_is_admin_only(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $administrativo = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO);
        $order = MaintenanceOrder::factory()
            ->withDiagnosis()
            ->status(MaintenanceOrderStatus::Delivered)
            ->create([
                'completed_at' => now()->subHour(),
                'delivered_at' => now(),
            ]);

        $this->actingAs($administrativo)
            ->post(route('maintenance-orders.reopen', $order), [
                'reason' => 'Retrabajo solicitado',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('maintenance-orders.reopen', $order), [
                'reason' => 'Retrabajo solicitado por garantía',
            ])
            ->assertRedirect();

        $this->assertSame(MaintenanceOrderStatus::InProgress, $order->fresh()->status);
    }

    public function test_completed_order_blocks_new_items(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $order = MaintenanceOrder::factory()
            ->withDiagnosis()
            ->status(MaintenanceOrderStatus::Completed)
            ->create();

        $this->actingAs($admin)
            ->post(route('maintenance-orders.items.store', $order), [
                'description' => 'Servicio extra',
                'quantity' => 1,
                'unit_price' => 100,
            ])
            ->assertForbidden();
    }

    public function test_can_upload_evidence_with_valid_mime(): void
    {
        Storage::fake('local');

        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $order = MaintenanceOrder::factory()->create();
        $file = UploadedFile::fake()->image('evidencia.jpg');

        $this->actingAs($admin)
            ->post(route('maintenance-orders.attachments.store', $order), [
                'file' => $file,
            ])
            ->assertRedirect();

        $this->assertSame(1, $order->attachments()->count());
    }

    public function test_index_filters_by_status(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        MaintenanceOrder::factory()->status(MaintenanceOrderStatus::Received)->create();
        MaintenanceOrder::factory()->status(MaintenanceOrderStatus::Delivered)->create();

        $this->actingAs($admin)
            ->get(route('maintenance-orders.index', ['status' => 'received']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('MaintenanceOrders/Index')
                ->has('orders.data', 1)
                ->where('orders.data.0.status', 'received'));
    }

    public function test_catalog_is_in_use_when_referenced_by_order_item(): void
    {
        $service = ServiceCatalog::factory()->create();
        $order = MaintenanceOrder::factory()->create();

        $this->assertFalse($service->isInUse());

        $order->items()->create([
            'service_catalog_id' => $service->id,
            'code' => $service->code,
            'description' => $service->description,
            'quantity' => 1,
            'unit_price' => $service->base_price,
            'discount' => 0,
            'line_total' => $service->base_price,
        ]);

        $this->assertTrue($service->fresh()->isInUse());
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
