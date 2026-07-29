<?php

namespace Tests\Feature\Catalogs;

use App\Actions\Catalogs\DeletePartCatalog;
use App\Enums\PartCatalogType;
use App\Models\PartCatalog;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class PartCatalogManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_create_part_catalog_item(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);

        $response = $this->actingAs($admin)->post(route('part-catalog.store'), [
            'code' => 'PRT-001',
            'description' => 'Filtro de aceite',
            'type' => PartCatalogType::Part->value,
            'service_category_id' => null,
            'base_price' => '320.00',
            'unit_of_measure' => 'pieza',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('part-catalog.index'));

        $part = PartCatalog::query()->where('code', 'PRT-001')->first();

        $this->assertNotNull($part);
        $this->assertSame('320.00', $part->base_price);
        $this->assertSame(PartCatalogType::Part, $part->type);
    }

    public function test_admin_can_deactivate_part(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $part = PartCatalog::factory()->create(['is_active' => true]);

        $this->actingAs($admin)
            ->post(route('part-catalog.deactivate', $part))
            ->assertRedirect(route('part-catalog.index'));

        $this->assertFalse($part->fresh()->is_active);
    }

    public function test_inactive_parts_excluded_from_active_scope(): void
    {
        PartCatalog::factory()->create(['code' => 'ACT-P', 'is_active' => true]);
        PartCatalog::factory()->inactive()->create(['code' => 'INA-P']);

        $codes = PartCatalog::query()->active()->pluck('code')->all();

        $this->assertContains('ACT-P', $codes);
        $this->assertNotContains('INA-P', $codes);
    }

    public function test_cannot_physically_delete_part_when_in_use_rn_cat_001(): void
    {
        $part = Mockery::mock(PartCatalog::factory()->create())->makePartial();
        $part->shouldReceive('isInUse')->andReturn(true);
        $part->shouldReceive('delete')->never();

        $this->expectException(ValidationException::class);

        (new DeletePartCatalog)->handle($part);
    }

    public function test_can_delete_part_when_not_in_use(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $part = PartCatalog::factory()->create();

        $this->assertFalse($part->isInUse());

        $this->actingAs($admin)
            ->delete(route('part-catalog.destroy', $part))
            ->assertRedirect(route('part-catalog.index'));

        $this->assertDatabaseMissing('part_catalog', ['id' => $part->id]);
    }

    public function test_non_admin_cannot_manage_part_catalog(): void
    {
        $consulta = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_CONSULTA);

        $this->actingAs($consulta)
            ->get(route('part-catalog.index'))
            ->assertForbidden();

        $this->actingAs($consulta)
            ->post(route('part-catalog.store'), [
                'code' => 'HACK',
                'description' => 'Hack',
                'type' => PartCatalogType::Other->value,
                'base_price' => '1.00',
                'unit_of_measure' => 'pieza',
            ])
            ->assertForbidden();
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
