<?php

namespace Tests\Feature\Reports;

use App\Enums\QuotationStatus;
use App\Models\MaintenanceOrder;
use App\Models\Quotation;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class QuotationsByStatusReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_quotations_report_filters_by_status_and_shows_conversion(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $order = MaintenanceOrder::factory()->create();

        Quotation::factory()->sent()->create(['created_at' => now()]);
        $accepted = Quotation::factory()->accepted()->create([
            'created_at' => now(),
            'maintenance_order_id' => $order->id,
        ]);
        Quotation::factory()->create([
            'status' => QuotationStatus::Rejected,
            'created_at' => now(),
        ]);
        Quotation::factory()->accepted()->create([
            'created_at' => now()->subMonths(3),
            'accepted_at' => now()->subMonths(3),
        ]);

        $this->actingAs($admin)
            ->get(route('reports.quotations', [
                'from' => now()->startOfMonth()->toDateString(),
                'to' => now()->endOfMonth()->toDateString(),
                'status' => QuotationStatus::Accepted->value,
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reports/QuotationsByStatus')
                ->where('filters.status', QuotationStatus::Accepted->value)
                ->has('quotations.data', 1)
                ->where('quotations.data.0.id', $accepted->id)
                ->where('quotations.data.0.converted', true)
                ->where('conversion.accepted', 1)
                ->where('conversion.converted_to_order', 1)
                ->where('conversion.rate', 100)
            );
    }

    public function test_limited_role_hides_quotation_totals(): void
    {
        $tecnico = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_TECNICO);
        Quotation::factory()->sent()->create(['total' => '999.00']);

        $this->actingAs($tecnico)
            ->get(route('reports.quotations'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('can.viewFull', false)
                ->where('quotations.data.0.total', null)
            );
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
