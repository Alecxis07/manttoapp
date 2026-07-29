<?php

namespace Tests\Feature;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesAndPermissionsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeds_four_roles_and_permission_catalog(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertSame(4, Role::query()->count());
        $this->assertSame(count(RolesAndPermissionsSeeder::PERMISSIONS), Permission::query()->count());

        $admin = Role::findByName(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $this->assertTrue($admin->hasPermissionTo('settings.*'));
        $this->assertTrue($admin->hasPermissionTo('maintenance_orders.reopen'));

        $consulta = Role::findByName(RolesAndPermissionsSeeder::ROLE_CONSULTA);
        $this->assertTrue($consulta->hasPermissionTo('customers.read'));
        $this->assertFalse($consulta->hasPermissionTo('customers.create'));

        $tecnico = Role::findByName(RolesAndPermissionsSeeder::ROLE_TECNICO);
        $this->assertTrue($tecnico->hasPermissionTo('maintenance_orders.diagnose'));
        $this->assertFalse($tecnico->hasPermissionTo('settings.*'));
    }
}
