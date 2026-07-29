<?php

namespace Tests\Feature\MaintenanceOrders;

use App\Enums\MaintenanceOrderStatus;
use App\Models\MaintenanceOrder;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceOrderAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_consulta_can_view_but_not_create(): void
    {
        $consulta = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_CONSULTA);
        $order = MaintenanceOrder::factory()->create();

        $this->actingAs($consulta)
            ->get(route('maintenance-orders.index'))
            ->assertOk();

        $this->actingAs($consulta)
            ->get(route('maintenance-orders.show', $order))
            ->assertOk();

        $this->actingAs($consulta)
            ->get(route('maintenance-orders.create'))
            ->assertForbidden();
    }

    public function test_tecnico_can_diagnose_administrativo_cannot(): void
    {
        $tecnico = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_TECNICO);
        $administrativo = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO);
        $order = MaintenanceOrder::factory()->status(MaintenanceOrderStatus::Diagnosing)->create();

        $this->actingAs($administrativo)
            ->post(route('maintenance-orders.diagnose', $order), [
                'diagnosis' => 'No debería poder',
            ])
            ->assertForbidden();

        $this->actingAs($tecnico)
            ->post(route('maintenance-orders.diagnose', $order), [
                'diagnosis' => 'Hallazgo técnico válido',
                'assigned_user_id' => $tecnico->id,
            ])
            ->assertRedirect();

        $this->assertSame('Hallazgo técnico válido', $order->fresh()->diagnosis);
    }

    public function test_role_based_status_transition_matrix(): void
    {
        $tecnico = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_TECNICO);
        $administrativo = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO);

        $pending = MaintenanceOrder::factory()
            ->withDiagnosis()
            ->status(MaintenanceOrderStatus::PendingApproval)
            ->create();

        $this->actingAs($tecnico)
            ->post(route('maintenance-orders.transition', $pending), [
                'status' => MaintenanceOrderStatus::Approved->value,
            ])
            ->assertSessionHasErrors('status');

        $this->actingAs($administrativo)
            ->post(route('maintenance-orders.transition', $pending), [
                'status' => MaintenanceOrderStatus::Approved->value,
            ])
            ->assertRedirect();

        $this->assertSame(MaintenanceOrderStatus::Approved, $pending->fresh()->status);
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
