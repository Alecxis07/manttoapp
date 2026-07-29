<?php

namespace Tests\Feature\Reports;

use App\Enums\MaintenanceOrderStatus;
use App\Models\Customer;
use App\Models\MaintenanceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_global_search_groups_results_by_entity(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);

        $customer = Customer::factory()->create(['name' => 'Acme Transportes SA']);
        $vehicle = Vehicle::factory()->create([
            'customer_id' => $customer->id,
            'license_plate' => 'ACM-99-01',
        ]);
        $order = MaintenanceOrder::factory()->forVehicle($vehicle)->create([
            'folio' => 'ORD-2026-00999',
            'status' => MaintenanceOrderStatus::Received,
        ]);

        $this->actingAs($admin)
            ->get(route('search', ['q' => 'Acme']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Search/Index')
                ->where('query', 'Acme')
                ->has('customers', 1)
                ->where('customers.0.id', $customer->id)
                ->has('vehicles', 1)
                ->where('vehicles.0.id', $vehicle->id)
                ->has('orders', 1)
                ->where('orders.0.id', $order->id)
            );
    }

    public function test_global_search_json_returns_grouped_payload(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        Vehicle::factory()->create(['license_plate' => 'XYZ-12-34']);

        $this->actingAs($admin)
            ->getJson(route('search', ['q' => 'XYZ1234', 'json' => 1]))
            ->assertOk()
            ->assertJsonPath('vehicles.0.license_plate', 'XYZ-12-34')
            ->assertJsonStructure([
                'query',
                'customers',
                'vehicles',
                'orders',
            ]);
    }

    public function test_user_without_report_permission_cannot_search(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $this->actingAs($user)
            ->get(route('search', ['q' => 'test']))
            ->assertForbidden();
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
