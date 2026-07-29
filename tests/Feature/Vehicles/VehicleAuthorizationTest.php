<?php

namespace Tests\Feature\Vehicles;

use App\Enums\VehicleStatus;
use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_administrativo_can_create_and_update_but_not_delete(): void
    {
        $user = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO);
        $customer = Customer::factory()->create();
        $type = VehicleType::factory()->create();

        $this->actingAs($user)
            ->post(route('vehicles.store'), [
                'customer_id' => $customer->id,
                'vehicle_type_id' => $type->id,
                'license_plate' => 'ADM-01-01',
                'brand' => 'Kenworth',
                'model' => 'T680',
                'year' => 2020,
                'current_mileage' => 0,
                'status' => VehicleStatus::Active->value,
            ])
            ->assertRedirect();

        $vehicle = Vehicle::query()->where('license_plate_normalized', 'ADM0101')->first();
        $this->assertNotNull($vehicle);

        $this->actingAs($user)
            ->put(route('customers.vehicles.update', [$customer, $vehicle]), [
                'customer_id' => $customer->id,
                'vehicle_type_id' => $type->id,
                'license_plate' => 'ADM-01-01',
                'brand' => 'Kenworth',
                'model' => 'T680X',
                'year' => 2020,
                'current_mileage' => 10,
                'status' => VehicleStatus::Active->value,
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->delete(route('customers.vehicles.destroy', [$customer, $vehicle]))
            ->assertForbidden();
    }

    public function test_tecnico_can_read_but_not_create(): void
    {
        $tecnico = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_TECNICO);
        $vehicle = Vehicle::factory()->create();

        $this->actingAs($tecnico)
            ->get(route('vehicles.index'))
            ->assertOk();

        $this->actingAs($tecnico)
            ->get(route('customers.vehicles.show', [$vehicle->customer_id, $vehicle->id]))
            ->assertOk();

        $this->actingAs($tecnico)
            ->post(route('vehicles.store'), [
                'customer_id' => $vehicle->customer_id,
                'vehicle_type_id' => $vehicle->vehicle_type_id,
                'license_plate' => 'TEC-01-01',
                'brand' => 'Volvo',
                'model' => 'VNL',
                'year' => 2019,
                'current_mileage' => 0,
                'status' => VehicleStatus::Active->value,
            ])
            ->assertForbidden();
    }

    public function test_consulta_cannot_update_vehicles(): void
    {
        $consulta = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_CONSULTA);
        $vehicle = Vehicle::factory()->create();

        $this->actingAs($consulta)
            ->get(route('vehicles.index'))
            ->assertOk();

        $this->actingAs($consulta)
            ->put(route('customers.vehicles.update', [$vehicle->customer_id, $vehicle->id]), [
                'customer_id' => $vehicle->customer_id,
                'vehicle_type_id' => $vehicle->vehicle_type_id,
                'license_plate' => $vehicle->license_plate,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'current_mileage' => $vehicle->current_mileage,
                'status' => $vehicle->status->value,
            ])
            ->assertForbidden();
    }

    public function test_scoped_binding_rejects_vehicle_from_other_customer(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $vehicle = Vehicle::factory()->create();
        $otherCustomer = Customer::factory()->create();

        $this->actingAs($admin)
            ->get(route('customers.vehicles.show', [$otherCustomer->id, $vehicle->id]))
            ->assertNotFound();
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
