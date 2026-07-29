<?php

namespace Tests\Feature\Reports;

use App\Enums\BillingRequestStatus;
use App\Enums\MaintenanceOrderStatus;
use App\Enums\QuotationStatus;
use App\Models\BillingRequest;
use App\Models\MaintenanceOrder;
use App\Models\Quotation;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardKpiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_dashboard_kpis_match_factory_data(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $vehicleA = Vehicle::factory()->create();
        $vehicleB = Vehicle::factory()->create();

        MaintenanceOrder::factory()->forVehicle($vehicleA)->status(MaintenanceOrderStatus::InProgress)->create();
        MaintenanceOrder::factory()->forVehicle($vehicleA)->status(MaintenanceOrderStatus::Received)->create();
        MaintenanceOrder::factory()->forVehicle($vehicleB)->create([
            'status' => MaintenanceOrderStatus::Completed,
            'completed_at' => now(),
            'total' => '1500.50',
        ]);
        MaintenanceOrder::factory()->forVehicle($vehicleB)->create([
            'status' => MaintenanceOrderStatus::Delivered,
            'completed_at' => now()->subMonth(),
            'total' => '999.00',
        ]);

        Quotation::factory()->sent()->create();
        Quotation::factory()->accepted()->create(['accepted_at' => now()]);
        Quotation::factory()->create(['status' => QuotationStatus::Draft]);

        BillingRequest::factory()->create(['status' => BillingRequestStatus::PendingReview]);
        BillingRequest::factory()->approved()->create();
        BillingRequest::factory()->processed()->create();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('kpis.active_orders', 2)
                ->where('kpis.completed_this_month', 1)
                ->where('kpis.pending_quotations', 1)
                ->where('kpis.accepted_quotations', 1)
                ->where('kpis.pending_billing', 2)
                ->where('kpis.period_revenue', '1500.50')
                ->where('kpis.units_served', 2)
                ->where('kpis.full_access', true)
                ->has('kpis.frequent_vehicles', 2)
                ->where('kpis.frequent_vehicles.0.orders_count', 2)
                ->where('kpis.frequent_vehicles.1.orders_count', 2)
            );
    }

    public function test_limited_role_does_not_receive_financial_kpis(): void
    {
        $tecnico = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_TECNICO);

        MaintenanceOrder::factory()->create([
            'status' => MaintenanceOrderStatus::Completed,
            'completed_at' => now(),
            'total' => '2500.00',
        ]);
        BillingRequest::factory()->create(['status' => BillingRequestStatus::PendingReview]);

        $this->actingAs($tecnico)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('kpis.full_access', false)
                ->where('kpis.completed_this_month', 1)
                ->where('kpis.pending_billing', null)
                ->where('kpis.period_revenue', null)
                ->where('kpis.units_served', null)
                ->where('kpis.frequent_vehicles', [])
            );
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
