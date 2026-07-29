<?php

namespace Tests\Feature\Vehicles;

use App\Enums\ExpedienteEventType;
use App\Enums\QuotationStatus;
use App\Models\Attachment;
use App\Models\MaintenanceOrder;
use App\Models\Quotation;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\VehicleExpedienteTimeline;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class VehicleExpedienteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_show_returns_consolidated_timeline_descending(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $vehicle = Vehicle::factory()->create();

        MaintenanceOrder::factory()->forVehicle($vehicle)->create([
            'folio' => 'ORD-2026-01000',
            'received_at' => Carbon::parse('2026-01-10 10:00:00'),
            'total' => '850.00',
        ]);

        Quotation::factory()->create([
            'customer_id' => $vehicle->customer_id,
            'vehicle_id' => $vehicle->id,
            'folio' => 'COT-2026-01000',
            'status' => QuotationStatus::Sent,
            'issued_at' => Carbon::parse('2026-03-01 10:00:00'),
            'total' => '1200.00',
        ]);

        Attachment::query()->create([
            'attachable_type' => $vehicle->getMorphClass(),
            'attachable_id' => $vehicle->id,
            'disk' => 'local',
            'path' => 'vehicles/a.pdf',
            'original_name' => 'evidencia.pdf',
            'mime_type' => 'application/pdf',
            'size' => 100,
            'uploaded_by' => $admin->id,
        ]);

        Attachment::query()
            ->where('original_name', 'evidencia.pdf')
            ->update([
                'created_at' => Carbon::parse('2026-02-01 10:00:00'),
                'updated_at' => Carbon::parse('2026-02-01 10:00:00'),
            ]);

        $this->actingAs($admin)
            ->get(route('customers.vehicles.show', [$vehicle->customer_id, $vehicle->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Vehicles/Show')
                ->has('timeline', 3)
                ->where('timeline.0.type', ExpedienteEventType::Quotation->value)
                ->where('timeline.1.type', ExpedienteEventType::Evidence->value)
                ->where('timeline.2.type', ExpedienteEventType::Order->value)
                ->where('timeline.2.amount', '850.00')
                ->where('can.exportHistory', true)
                ->has('eventTypes'));
    }

    public function test_timeline_filters_by_date_and_type(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $vehicle = Vehicle::factory()->create();

        MaintenanceOrder::factory()->forVehicle($vehicle)->create([
            'received_at' => Carbon::parse('2026-01-05 08:00:00'),
        ]);

        Quotation::factory()->create([
            'customer_id' => $vehicle->customer_id,
            'vehicle_id' => $vehicle->id,
            'issued_at' => Carbon::parse('2026-04-10 08:00:00'),
            'total' => '999.00',
        ]);

        $this->actingAs($admin)
            ->get(route('customers.vehicles.show', [
                'customer' => $vehicle->customer_id,
                'vehicle' => $vehicle->id,
                'from' => '2026-04-01',
                'to' => '2026-04-30',
                'type' => ExpedienteEventType::Quotation->value,
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('timeline', 1)
                ->where('timeline.0.type', ExpedienteEventType::Quotation->value)
                ->where('timeline.0.amount', '999.00')
                ->where('historyFilters.type', ExpedienteEventType::Quotation->value));
    }

    public function test_pdf_export_is_generated_with_filters(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $vehicle = Vehicle::factory()->create([
            'license_plate' => 'EXP-99-01',
        ]);

        MaintenanceOrder::factory()->forVehicle($vehicle)->create([
            'folio' => 'ORD-2026-02000',
            'received_at' => Carbon::parse('2026-05-01 10:00:00'),
            'total' => '1500.50',
        ]);

        $response = $this->actingAs($admin)->get(route('customers.vehicles.history.pdf', [
            'customer' => $vehicle->customer_id,
            'vehicle' => $vehicle->id,
            'type' => ExpedienteEventType::Order->value,
        ]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString(
            'expediente-EXP9901',
            (string) $response->headers->get('content-disposition')
        );

        $html = view('pdf.vehicle-history', [
            'vehicle' => $vehicle->fresh(['customer', 'vehicleType']),
            'customer' => $vehicle->customer,
            'entries' => app(VehicleExpedienteTimeline::class)->build(
                $vehicle,
                types: [ExpedienteEventType::Order->value],
            ),
            'filters' => ['from' => null, 'to' => null, 'types' => [ExpedienteEventType::Order->value]],
            'generatedAt' => now(),
        ])->render();

        $this->assertStringContainsString('ORD-2026-02000', $html);
        $this->assertStringContainsString('1,500.50', $html);
        $this->assertStringContainsString($vehicle->license_plate, $html);
    }

    public function test_guest_cannot_view_expediente_or_export_pdf(): void
    {
        $vehicle = Vehicle::factory()->create();

        $this->get(route('customers.vehicles.show', [$vehicle->customer_id, $vehicle->id]))
            ->assertRedirect();

        $this->get(route('customers.vehicles.history.pdf', [$vehicle->customer_id, $vehicle->id]))
            ->assertRedirect();
    }

    public function test_user_without_vehicles_read_cannot_export_pdf(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $vehicle = Vehicle::factory()->create();

        $this->actingAs($user)
            ->get(route('customers.vehicles.history.pdf', [$vehicle->customer_id, $vehicle->id]))
            ->assertForbidden();
    }

    public function test_tecnico_can_view_and_export_history(): void
    {
        $tecnico = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_TECNICO);
        $vehicle = Vehicle::factory()->create();

        MaintenanceOrder::factory()->forVehicle($vehicle)->create([
            'received_at' => now()->subDay(),
        ]);

        $this->actingAs($tecnico)
            ->get(route('customers.vehicles.show', [$vehicle->customer_id, $vehicle->id]))
            ->assertOk();

        $response = $this->actingAs($tecnico)
            ->get(route('customers.vehicles.history.pdf', [$vehicle->customer_id, $vehicle->id]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
