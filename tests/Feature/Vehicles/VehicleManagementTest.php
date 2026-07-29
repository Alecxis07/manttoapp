<?php

namespace Tests\Feature\Vehicles;

use App\Enums\CustomerStatus;
use App\Enums\VehicleStatus;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\MaintenanceOrder;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class VehicleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_create_vehicle_with_normalized_plate(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $customer = Customer::factory()->create(['status' => CustomerStatus::Active]);
        $type = VehicleType::factory()->create();

        $response = $this->actingAs($admin)->post(route('customers.vehicles.store', $customer), [
            'customer_id' => $customer->id,
            'vehicle_type_id' => $type->id,
            'license_plate' => 'abc-12-34',
            'vin' => '1hgbh41jxmn109186',
            'economic_number' => 'ECO-100',
            'brand' => 'Kenworth',
            'model' => 'T680',
            'year' => 2020,
            'engine_type' => 'Diésel',
            'current_mileage' => 10000,
            'status' => VehicleStatus::Active->value,
        ]);

        $vehicle = Vehicle::query()->where('license_plate_normalized', 'ABC1234')->first();

        $this->assertNotNull($vehicle);
        $response->assertRedirect(route('customers.vehicles.show', [$customer, $vehicle]));
        $this->assertSame('abc-12-34', $vehicle->license_plate);
        $this->assertSame('ABC1234', $vehicle->license_plate_normalized);
        $this->assertSame('1HGBH41JXMN109186', $vehicle->vin);
        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => Vehicle::class,
            'subject_id' => $vehicle->id,
            'action' => 'created',
        ]);
    }

    public function test_duplicate_normalized_plates_are_rejected(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $customer = Customer::factory()->create();
        $type = VehicleType::factory()->create();
        Vehicle::factory()->create([
            'customer_id' => $customer->id,
            'vehicle_type_id' => $type->id,
            'license_plate' => 'ABC-123',
        ]);

        $this->actingAs($admin)
            ->post(route('vehicles.store'), [
                'customer_id' => $customer->id,
                'vehicle_type_id' => $type->id,
                'license_plate' => 'abc 123',
                'brand' => 'Volvo',
                'model' => 'VNL',
                'year' => 2019,
                'current_mileage' => 0,
                'status' => VehicleStatus::Active->value,
            ])
            ->assertSessionHasErrors('license_plate');
    }

    public function test_vin_must_be_unique_when_provided(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $customer = Customer::factory()->create();
        $type = VehicleType::factory()->create();
        Vehicle::factory()->create([
            'customer_id' => $customer->id,
            'vehicle_type_id' => $type->id,
            'vin' => 'VIN1234567890ABCD',
        ]);

        $this->actingAs($admin)
            ->post(route('vehicles.store'), [
                'customer_id' => $customer->id,
                'vehicle_type_id' => $type->id,
                'license_plate' => 'XYZ-99-99',
                'vin' => 'vin1234567890abcd',
                'brand' => 'Volvo',
                'model' => 'VNL',
                'year' => 2019,
                'current_mileage' => 0,
                'status' => VehicleStatus::Active->value,
            ])
            ->assertSessionHasErrors('vin');
    }

    public function test_null_vins_do_not_collide(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $customer = Customer::factory()->create();
        $type = VehicleType::factory()->create();
        Vehicle::factory()->withoutVin()->create([
            'customer_id' => $customer->id,
            'vehicle_type_id' => $type->id,
        ]);

        $this->actingAs($admin)
            ->post(route('vehicles.store'), [
                'customer_id' => $customer->id,
                'vehicle_type_id' => $type->id,
                'license_plate' => 'NEW-01-01',
                'vin' => null,
                'brand' => 'Scania',
                'model' => 'R450',
                'year' => 2021,
                'current_mileage' => 0,
                'status' => VehicleStatus::Active->value,
            ])
            ->assertRedirect();

        $this->assertSame(2, Vehicle::query()->whereNull('vin')->count());
    }

    public function test_economic_number_unique_per_customer(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $customer = Customer::factory()->create();
        $other = Customer::factory()->create();
        $type = VehicleType::factory()->create();

        Vehicle::factory()->create([
            'customer_id' => $customer->id,
            'vehicle_type_id' => $type->id,
            'economic_number' => 'ECO-1',
        ]);

        $this->actingAs($admin)
            ->post(route('vehicles.store'), [
                'customer_id' => $customer->id,
                'vehicle_type_id' => $type->id,
                'license_plate' => 'PLATE-A',
                'economic_number' => 'ECO-1',
                'brand' => 'Kenworth',
                'model' => 'T680',
                'year' => 2020,
                'current_mileage' => 0,
                'status' => VehicleStatus::Active->value,
            ])
            ->assertSessionHasErrors('economic_number');

        $this->actingAs($admin)
            ->post(route('vehicles.store'), [
                'customer_id' => $other->id,
                'vehicle_type_id' => $type->id,
                'license_plate' => 'PLATE-B',
                'economic_number' => 'ECO-1',
                'brand' => 'Kenworth',
                'model' => 'T680',
                'year' => 2020,
                'current_mileage' => 0,
                'status' => VehicleStatus::Active->value,
            ])
            ->assertRedirect();
    }

    public function test_cannot_assign_vehicle_to_inactive_customer(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $customer = Customer::factory()->inactive()->create();
        $type = VehicleType::factory()->create();

        $this->actingAs($admin)
            ->post(route('vehicles.store'), [
                'customer_id' => $customer->id,
                'vehicle_type_id' => $type->id,
                'license_plate' => 'INACT-01',
                'brand' => 'Volvo',
                'model' => 'VNL',
                'year' => 2018,
                'current_mileage' => 0,
                'status' => VehicleStatus::Active->value,
            ])
            ->assertSessionHasErrors('customer_id');
    }

    public function test_mileage_cannot_decrease(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $vehicle = Vehicle::factory()->create(['current_mileage' => 50000]);

        $this->actingAs($admin)
            ->put(route('customers.vehicles.update', [$vehicle->customer_id, $vehicle->id]), [
                'customer_id' => $vehicle->customer_id,
                'vehicle_type_id' => $vehicle->vehicle_type_id,
                'license_plate' => $vehicle->license_plate,
                'vin' => $vehicle->vin,
                'economic_number' => $vehicle->economic_number,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'engine_type' => $vehicle->engine_type,
                'current_mileage' => 49999,
                'status' => $vehicle->status->value,
            ])
            ->assertSessionHasErrors('current_mileage');
    }

    public function test_status_change_is_audited(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $vehicle = Vehicle::factory()->create(['status' => VehicleStatus::Active]);

        $this->actingAs($admin)
            ->put(route('customers.vehicles.update', [$vehicle->customer_id, $vehicle->id]), [
                'customer_id' => $vehicle->customer_id,
                'vehicle_type_id' => $vehicle->vehicle_type_id,
                'license_plate' => $vehicle->license_plate,
                'vin' => $vehicle->vin,
                'economic_number' => $vehicle->economic_number,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'engine_type' => $vehicle->engine_type,
                'current_mileage' => $vehicle->current_mileage,
                'status' => VehicleStatus::Inactive->value,
                'status_notes' => 'Fuera de operación temporal',
            ])
            ->assertRedirect();

        $vehicle->refresh();
        $this->assertSame(VehicleStatus::Inactive, $vehicle->status);
        $this->assertSame('Fuera de operación temporal', $vehicle->status_notes);

        $this->assertTrue(
            ActivityLog::query()
                ->where('subject_type', Vehicle::class)
                ->where('subject_id', $vehicle->id)
                ->where('action', 'updated')
                ->exists()
        );
    }

    public function test_cannot_delete_vehicle_with_maintenance_orders(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $vehicle = Vehicle::factory()->create();

        MaintenanceOrder::factory()->forVehicle($vehicle)->create([
            'folio' => 'ORD-2026-00001',
            'status' => 'received',
        ]);

        $this->actingAs($admin)
            ->delete(route('customers.vehicles.destroy', [$vehicle->customer_id, $vehicle->id]))
            ->assertSessionHasErrors('vehicle');

        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'deleted_at' => null]);
    }

    public function test_can_delete_vehicle_without_orders(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $vehicle = Vehicle::factory()->create();

        $this->actingAs($admin)
            ->delete(route('customers.vehicles.destroy', [$vehicle->customer_id, $vehicle->id]))
            ->assertRedirect(route('vehicles.index'));

        $this->assertSoftDeleted('vehicles', ['id' => $vehicle->id]);
    }

    public function test_cannot_deactivate_customer_with_active_vehicles(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $customer = Customer::factory()->create(['status' => CustomerStatus::Active]);
        Vehicle::factory()->create([
            'customer_id' => $customer->id,
            'status' => VehicleStatus::Active,
        ]);

        $this->actingAs($admin)
            ->delete(route('customers.destroy', $customer))
            ->assertSessionHasErrors('customer');

        $this->assertSame(CustomerStatus::Active, $customer->fresh()->status);
    }

    public function test_show_renders_expediente_tabs_data(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $vehicle = Vehicle::factory()->create();

        $this->actingAs($admin)
            ->get(route('customers.vehicles.show', [$vehicle->customer_id, $vehicle->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Vehicles/Show')
                ->has('vehicle')
                ->has('attachments')
                ->has('auditHistory')
                ->has('timeline')
                ->has('historyFilters')
                ->has('eventTypes')
                ->where('can.exportHistory', true));
    }

    public function test_can_attach_file_when_creating_vehicle(): void
    {
        Storage::fake('local');

        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $customer = Customer::factory()->create();
        $type = VehicleType::factory()->create();
        $file = UploadedFile::fake()->image('tarjeta.jpg');

        $this->actingAs($admin)
            ->post(route('vehicles.store'), [
                'customer_id' => $customer->id,
                'vehicle_type_id' => $type->id,
                'license_plate' => 'ATT-01-01',
                'brand' => 'Kenworth',
                'model' => 'T680',
                'year' => 2022,
                'current_mileage' => 100,
                'status' => VehicleStatus::Active->value,
                'attachments' => [$file],
            ])
            ->assertRedirect();

        $vehicle = Vehicle::query()->where('license_plate_normalized', 'ATT0101')->first();
        $this->assertNotNull($vehicle);
        $this->assertSame(1, $vehicle->attachments()->count());
    }

    public function test_index_supports_filters(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        Vehicle::factory()->create([
            'license_plate' => 'FIND-ME',
            'brand' => 'Freightliner',
            'status' => VehicleStatus::Active,
        ]);
        Vehicle::factory()->inactive()->create([
            'license_plate' => 'OTHER',
            'brand' => 'Volvo',
        ]);

        $this->actingAs($admin)
            ->get(route('vehicles.index', ['search' => 'FIND', 'status' => 'active']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Vehicles/Index')
                ->has('vehicles.data', 1)
                ->where('vehicles.data.0.license_plate', 'FIND-ME'));
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
