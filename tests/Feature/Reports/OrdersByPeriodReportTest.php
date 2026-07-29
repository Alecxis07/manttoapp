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

class OrdersByPeriodReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_orders_report_filters_by_period_and_status_in_url(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

        $inRange = MaintenanceOrder::factory()->forVehicle($vehicle)->create([
            'status' => MaintenanceOrderStatus::Completed,
            'received_at' => now()->startOfMonth()->addDays(2),
            'completed_at' => now(),
            'total' => '800.00',
        ]);
        MaintenanceOrder::factory()->forVehicle($vehicle)->create([
            'status' => MaintenanceOrderStatus::Received,
            'received_at' => now()->subMonths(2),
            'total' => '100.00',
        ]);
        MaintenanceOrder::factory()->forVehicle($vehicle)->create([
            'status' => MaintenanceOrderStatus::InProgress,
            'received_at' => now()->startOfMonth()->addDay(),
            'total' => '50.00',
        ]);

        $this->actingAs($admin)
            ->get(route('reports.orders', [
                'from' => now()->startOfMonth()->toDateString(),
                'to' => now()->endOfMonth()->toDateString(),
                'status' => MaintenanceOrderStatus::Completed->value,
                'date_field' => 'received_at',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reports/OrdersByPeriod')
                ->where('filters.status', MaintenanceOrderStatus::Completed->value)
                ->where('summary.count', 1)
                ->where('summary.total_amount', '800.00')
                ->has('orders.data', 1)
                ->where('orders.data.0.id', $inRange->id)
                ->where('orders.data.0.total', '800.00')
                ->where('can.viewFull', true)
            );
    }

    public function test_limited_role_hides_order_totals(): void
    {
        $tecnico = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_TECNICO);
        MaintenanceOrder::factory()->create([
            'received_at' => now(),
            'total' => '1200.00',
        ]);

        $this->actingAs($tecnico)
            ->get(route('reports.orders', [
                'from' => now()->startOfMonth()->toDateString(),
                'to' => now()->endOfMonth()->toDateString(),
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('can.viewFull', false)
                ->where('summary.total_amount', null)
                ->where('orders.data.0.total', null)
            );
    }

    public function test_orders_csv_export_requires_export_permission(): void
    {
        $consulta = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_CONSULTA);
        MaintenanceOrder::factory()->create(['received_at' => now()]);

        $this->actingAs($consulta)
            ->get(route('reports.orders', [
                'from' => now()->startOfMonth()->toDateString(),
                'to' => now()->endOfMonth()->toDateString(),
                'export' => 1,
            ]))
            ->assertForbidden();
    }

    public function test_admin_can_export_orders_csv(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        MaintenanceOrder::factory()->create([
            'folio' => 'ORD-2026-00042',
            'received_at' => now(),
            'total' => '300.00',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('reports.orders', [
                'from' => now()->startOfMonth()->toDateString(),
                'to' => now()->endOfMonth()->toDateString(),
                'export' => 1,
            ]));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('ORD-2026-00042', $response->streamedContent());
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
