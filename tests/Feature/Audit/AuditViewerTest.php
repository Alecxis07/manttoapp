<?php

namespace Tests\Feature\Audit;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuditViewerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_and_administrativo_can_view_audit_log(): void
    {
        foreach ([
            RolesAndPermissionsSeeder::ROLE_ADMIN,
            RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO,
        ] as $role) {
            $user = $this->makeUserWithRole($role);

            ActivityLog::query()->create([
                'user_id' => $user->id,
                'action' => 'created',
                'subject_type' => Customer::class,
                'subject_id' => 1,
                'properties' => ['after' => ['name' => 'Demo']],
                'created_at' => now(),
            ]);

            $this->actingAs($user)
                ->get(route('audit.index', [
                    'entity' => Customer::class,
                    'action' => 'created',
                    'user_id' => $user->id,
                ]))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('Audit/Index')
                    ->has('logs.data', 1)
                    ->where('logs.data.0.subject_type', Customer::class)
                );
        }
    }

    public function test_tecnico_and_consulta_cannot_view_audit_log(): void
    {
        foreach ([
            RolesAndPermissionsSeeder::ROLE_TECNICO,
            RolesAndPermissionsSeeder::ROLE_CONSULTA,
        ] as $role) {
            $user = $this->makeUserWithRole($role);

            $this->actingAs($user)
                ->get(route('audit.index'))
                ->assertForbidden();
        }
    }

    public function test_audit_viewer_filters_by_entity_user_action_and_date(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $other = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO);

        ActivityLog::query()->create([
            'user_id' => $admin->id,
            'action' => 'updated',
            'subject_type' => Customer::class,
            'subject_id' => 10,
            'properties' => ['before' => ['name' => 'A'], 'after' => ['name' => 'B']],
            'created_at' => now()->subDay(),
        ]);

        ActivityLog::query()->create([
            'user_id' => $other->id,
            'action' => 'created',
            'subject_type' => User::class,
            'subject_id' => 20,
            'properties' => ['after' => ['name' => 'X']],
            'created_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('audit.index', [
                'entity' => Customer::class,
                'user_id' => $admin->id,
                'action' => 'updated',
                'from' => now()->subDays(2)->toDateString(),
                'to' => now()->subDay()->toDateString(),
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Audit/Index')
                ->has('logs.data', 1)
                ->where('logs.data.0.action', 'updated')
                ->where('logs.data.0.subject_type', Customer::class)
                ->where('logs.data.0.user.id', $admin->id)
            );
    }

    private function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
