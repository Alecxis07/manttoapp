<?php

namespace Tests\Feature\Reports;

use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReportAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_consulta_can_view_limited_reports(): void
    {
        $consulta = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_CONSULTA);

        $this->actingAs($consulta)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reports/Index')
                ->where('can.viewFull', false)
                ->where('can.export', false)
            );
    }

    public function test_user_without_report_permissions_is_forbidden(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $this->actingAs($user)
            ->get(route('reports.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('reports.orders'))
            ->assertForbidden();
    }

    public function test_vehicle_history_report_eager_loads_and_paginates(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $vehicle = Vehicle::factory()->create();

        $this->actingAs($admin)
            ->get(route('reports.vehicle-history', [
                'vehicle_id' => $vehicle->id,
                'from' => now()->subYear()->toDateString(),
                'to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reports/VehicleHistory')
                ->where('vehicle.id', $vehicle->id)
                ->has('orders.data')
                ->where('filters.vehicle_id', $vehicle->id)
            );
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
