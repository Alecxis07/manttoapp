<?php

namespace Tests\Feature\Catalogs;

use App\Actions\Catalogs\DeleteServiceCatalog;
use App\Enums\ServiceCatalogType;
use App\Models\ServiceCatalog;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class ServiceCatalogManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_create_service_catalog_item(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $category = ServiceCategory::factory()->create();

        $response = $this->actingAs($admin)->post(route('service-catalog.store'), [
            'code' => 'SRV-001',
            'description' => 'Cambio de aceite',
            'service_category_id' => $category->id,
            'type' => ServiceCatalogType::Preventive->value,
            'base_price' => '850.50',
            'unit_of_measure' => 'servicio',
            'estimated_minutes' => 60,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('service-catalog.index'));

        $service = ServiceCatalog::query()->where('code', 'SRV-001')->first();

        $this->assertNotNull($service);
        $this->assertSame('850.50', $service->base_price);
        $this->assertTrue($service->is_active);
        $this->assertSame(ServiceCatalogType::Preventive, $service->type);
    }

    public function test_admin_can_deactivate_service(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $service = ServiceCatalog::factory()->create(['is_active' => true]);

        $this->actingAs($admin)
            ->post(route('service-catalog.deactivate', $service))
            ->assertRedirect(route('service-catalog.index'));

        $this->assertFalse($service->fresh()->is_active);
    }

    public function test_inactive_services_are_excluded_from_active_scope(): void
    {
        ServiceCatalog::factory()->create(['is_active' => true, 'code' => 'ACT-1']);
        ServiceCatalog::factory()->inactive()->create(['code' => 'INA-1']);

        $activeCodes = ServiceCatalog::query()->active()->pluck('code')->all();

        $this->assertContains('ACT-1', $activeCodes);
        $this->assertNotContains('INA-1', $activeCodes);
    }

    public function test_inactive_category_cannot_be_selected_for_new_service(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $inactive = ServiceCategory::factory()->inactive()->create();

        $this->actingAs($admin)
            ->post(route('service-catalog.store'), [
                'code' => 'SRV-BAD',
                'description' => 'Servicio inválido',
                'service_category_id' => $inactive->id,
                'type' => ServiceCatalogType::Corrective->value,
                'base_price' => '100.00',
                'unit_of_measure' => 'servicio',
                'is_active' => true,
            ])
            ->assertSessionHasErrors('service_category_id');
    }

    public function test_cannot_physically_delete_service_when_in_use_rn_cat_001(): void
    {
        $service = Mockery::mock(ServiceCatalog::factory()->create())->makePartial();
        $service->shouldReceive('isInUse')->andReturn(true);
        $service->shouldReceive('delete')->never();

        $this->expectException(ValidationException::class);

        (new DeleteServiceCatalog)->handle($service);
    }

    public function test_can_physically_delete_service_when_not_in_use(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $service = ServiceCatalog::factory()->create();

        $this->assertFalse($service->isInUse());

        $this->actingAs($admin)
            ->delete(route('service-catalog.destroy', $service))
            ->assertRedirect(route('service-catalog.index'));

        $this->assertDatabaseMissing('service_catalog', ['id' => $service->id]);
    }

    public function test_non_admin_cannot_manage_service_catalog(): void
    {
        $tecnico = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_TECNICO);

        $this->actingAs($tecnico)
            ->get(route('service-catalog.index'))
            ->assertForbidden();
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
