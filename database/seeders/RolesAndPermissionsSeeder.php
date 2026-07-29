<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Permissions from SDD/specs/03-actors-permissions.md §3,
     * extended with catalog permissions (admin-managed; RF-CAT-*).
     *
     * @var list<string>
     */
    public const PERMISSIONS = [
        'users.*',
        'roles.*',
        'customers.create',
        'customers.update',
        'customers.delete',
        'customers.read',
        'vehicles.create',
        'vehicles.update',
        'vehicles.delete',
        'vehicles.read',
        'service_categories.create',
        'service_categories.update',
        'service_categories.delete',
        'service_categories.read',
        'service_catalog.create',
        'service_catalog.update',
        'service_catalog.delete',
        'service_catalog.read',
        'part_catalog.create',
        'part_catalog.update',
        'part_catalog.delete',
        'part_catalog.read',
        'maintenance_orders.create',
        'maintenance_orders.update',
        'maintenance_orders.diagnose',
        'maintenance_orders.add_items',
        'maintenance_orders.change_status',
        'maintenance_orders.reopen',
        'maintenance_orders.delete',
        'maintenance_orders.read',
        'quotations.create',
        'quotations.update',
        'quotations.approve_discount',
        'quotations.read',
        'billing_requests.create',
        'billing_requests.update',
        'billing_requests.read',
        'reports.full',
        'reports.limited',
        'exports.full',
        'exports.basic',
        'attachments.*',
        'audit.view',
        'settings.*',
    ];

    public const ROLE_ADMIN = 'admin';

    public const ROLE_ADMINISTRATIVO = 'administrativo';

    public const ROLE_TECNICO = 'tecnico';

    public const ROLE_CONSULTA = 'consulta';

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission);
        }

        $admin = Role::findOrCreate(self::ROLE_ADMIN);
        $admin->syncPermissions(self::PERMISSIONS);

        $administrativo = Role::findOrCreate(self::ROLE_ADMINISTRATIVO);
        $administrativo->syncPermissions([
            'customers.create',
            'customers.update',
            'customers.read',
            'vehicles.create',
            'vehicles.update',
            'vehicles.read',
            'maintenance_orders.create',
            'maintenance_orders.update',
            'maintenance_orders.add_items',
            'maintenance_orders.change_status',
            'maintenance_orders.read',
            'quotations.create',
            'quotations.update',
            'quotations.approve_discount',
            'quotations.read',
            'billing_requests.create',
            'billing_requests.update',
            'billing_requests.read',
            'reports.full',
            'reports.limited',
            'exports.full',
            'exports.basic',
            'attachments.*',
            'audit.view',
        ]);

        $tecnico = Role::findOrCreate(self::ROLE_TECNICO);
        $tecnico->syncPermissions([
            'customers.read',
            'vehicles.read',
            'maintenance_orders.create',
            'maintenance_orders.update',
            'maintenance_orders.diagnose',
            'maintenance_orders.add_items',
            'maintenance_orders.change_status',
            'maintenance_orders.read',
            'quotations.create',
            'quotations.read',
            'billing_requests.read',
            'reports.limited',
            'exports.basic',
            'attachments.*',
        ]);

        $consulta = Role::findOrCreate(self::ROLE_CONSULTA);
        $consulta->syncPermissions([
            'customers.read',
            'vehicles.read',
            'maintenance_orders.read',
            'quotations.read',
            'billing_requests.read',
            'reports.limited',
        ]);
    }
}
