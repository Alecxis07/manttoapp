<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_has_critical_permissions(): void
    {
        $admin = $this->userWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);

        foreach ([
            'users.*',
            'roles.*',
            'customers.create',
            'customers.delete',
            'service_categories.create',
            'service_catalog.create',
            'part_catalog.create',
            'maintenance_orders.reopen',
            'settings.*',
            'audit.view',
        ] as $permission) {
            $this->assertTrue($admin->can($permission), "Admin should have {$permission}");
        }
    }

    public function test_administrativo_permission_boundaries(): void
    {
        $user = $this->userWithRole(RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO);

        $this->assertTrue($user->can('customers.create'));
        $this->assertTrue($user->can('customers.update'));
        $this->assertTrue($user->can('quotations.approve_discount'));
        $this->assertTrue($user->can('audit.view'));

        $this->assertFalse($user->can('users.*'));
        $this->assertFalse($user->can('roles.*'));
        $this->assertFalse($user->can('customers.delete'));
        $this->assertFalse($user->can('service_categories.create'));
        $this->assertFalse($user->can('service_catalog.update'));
        $this->assertFalse($user->can('part_catalog.delete'));
        $this->assertFalse($user->can('maintenance_orders.reopen'));
        $this->assertFalse($user->can('settings.*'));
    }

    public function test_tecnico_permission_boundaries(): void
    {
        $user = $this->userWithRole(RolesAndPermissionsSeeder::ROLE_TECNICO);

        $this->assertTrue($user->can('customers.read'));
        $this->assertTrue($user->can('maintenance_orders.diagnose'));
        $this->assertTrue($user->can('attachments.*'));

        $this->assertFalse($user->can('customers.create'));
        $this->assertFalse($user->can('users.*'));
        $this->assertFalse($user->can('quotations.update'));
        $this->assertFalse($user->can('billing_requests.create'));
        $this->assertFalse($user->can('audit.view'));
        $this->assertFalse($user->can('settings.*'));
    }

    public function test_consulta_is_read_only(): void
    {
        $user = $this->userWithRole(RolesAndPermissionsSeeder::ROLE_CONSULTA);

        foreach ([
            'customers.read',
            'vehicles.read',
            'maintenance_orders.read',
            'quotations.read',
            'billing_requests.read',
            'reports.limited',
        ] as $permission) {
            $this->assertTrue($user->can($permission), "Consulta should have {$permission}");
        }

        foreach ([
            'customers.create',
            'vehicles.update',
            'maintenance_orders.create',
            'quotations.create',
            'billing_requests.create',
            'users.*',
            'attachments.*',
            'audit.view',
            'settings.*',
        ] as $permission) {
            $this->assertFalse($user->can($permission), "Consulta should not have {$permission}");
        }
    }

    public function test_direct_route_access_denied_without_users_permission(): void
    {
        foreach ([
            RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO,
            RolesAndPermissionsSeeder::ROLE_TECNICO,
            RolesAndPermissionsSeeder::ROLE_CONSULTA,
        ] as $role) {
            $user = $this->userWithRole($role);

            $this->actingAs($user)
                ->get(route('users.index'))
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('users.create'))
                ->assertForbidden();
        }
    }

    public function test_seeded_roles_match_spec_matrix_counts(): void
    {
        $this->assertSame(4, Role::query()->count());

        $admin = Role::findByName(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $this->assertSame(count(RolesAndPermissionsSeeder::PERMISSIONS), $admin->permissions->count());

        $consulta = Role::findByName(RolesAndPermissionsSeeder::ROLE_CONSULTA);
        $this->assertFalse($consulta->hasPermissionTo('users.*'));
        $this->assertTrue($consulta->hasPermissionTo('customers.read'));
    }

    protected function userWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
