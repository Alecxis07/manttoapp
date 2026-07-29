<?php

namespace Tests\Feature\Catalogs;

use App\Actions\Catalogs\DeleteServiceCategory;
use App\Models\ServiceCatalog;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ServiceCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_create_service_category(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);

        $response = $this->actingAs($admin)->post(route('service-categories.store'), [
            'code' => 'MEC',
            'name' => 'Mecánica',
            'description' => 'Servicios mecánicos',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('service-categories.index'));
        $this->assertDatabaseHas('service_categories', [
            'code' => 'MEC',
            'name' => 'Mecánica',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_deactivate_category(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $category = ServiceCategory::factory()->create(['is_active' => true]);

        $this->actingAs($admin)
            ->post(route('service-categories.deactivate', $category))
            ->assertRedirect(route('service-categories.index'));

        $this->assertFalse($category->fresh()->is_active);
    }

    public function test_category_with_services_cannot_be_deleted_rn_cat_001(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $category = ServiceCategory::factory()->create();
        ServiceCatalog::factory()->create(['service_category_id' => $category->id]);

        $this->assertTrue($category->isInUse());

        $this->actingAs($admin)
            ->from(route('service-categories.index'))
            ->delete(route('service-categories.destroy', $category))
            ->assertRedirect(route('service-categories.index'))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseHas('service_categories', ['id' => $category->id]);
    }

    public function test_unused_category_can_be_deleted(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $category = ServiceCategory::factory()->create();

        $this->actingAs($admin)
            ->delete(route('service-categories.destroy', $category))
            ->assertRedirect(route('service-categories.index'));

        $this->assertDatabaseMissing('service_categories', ['id' => $category->id]);
    }

    public function test_delete_action_rejects_in_use_category(): void
    {
        $category = ServiceCategory::factory()->create();
        ServiceCatalog::factory()->create(['service_category_id' => $category->id]);

        $this->expectException(ValidationException::class);

        (new DeleteServiceCategory)->handle($category);
    }

    public function test_inactive_categories_excluded_from_active_scope(): void
    {
        ServiceCategory::factory()->create(['code' => 'ACT', 'is_active' => true]);
        ServiceCategory::factory()->inactive()->create(['code' => 'INA']);

        $codes = ServiceCategory::query()->active()->pluck('code')->all();

        $this->assertContains('ACT', $codes);
        $this->assertNotContains('INA', $codes);
    }

    public function test_non_admin_cannot_manage_categories(): void
    {
        foreach ([
            RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO,
            RolesAndPermissionsSeeder::ROLE_TECNICO,
            RolesAndPermissionsSeeder::ROLE_CONSULTA,
        ] as $role) {
            $user = $this->makeUserWithRole($role);

            $this->actingAs($user)
                ->get(route('service-categories.index'))
                ->assertForbidden();
        }
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
