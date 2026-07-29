<?php

namespace Tests\Feature\Users;

use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_view_users_index(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk();
    }

    public function test_admin_can_create_user_and_assign_role(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Nuevo Técnico',
            'email' => 'tecnico@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'status' => UserStatus::Active->value,
            'role' => RolesAndPermissionsSeeder::ROLE_TECNICO,
        ]);

        $response->assertRedirect(route('users.index'));

        $created = User::query()->where('email', 'tecnico@example.com')->first();

        $this->assertNotNull($created);
        $this->assertTrue($created->hasRole(RolesAndPermissionsSeeder::ROLE_TECNICO));
        $this->assertSame(UserStatus::Active, $created->status);
    }

    public function test_admin_can_update_user_role_and_status(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $target = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_CONSULTA);

        $response = $this->actingAs($admin)->put(route('users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'password' => '',
            'password_confirmation' => '',
            'status' => UserStatus::Inactive->value,
            'role' => RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO,
        ]);

        $response->assertRedirect(route('users.index'));

        $target->refresh();

        $this->assertSame(UserStatus::Inactive, $target->status);
        $this->assertTrue($target->hasRole(RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO));
        $this->assertFalse($target->hasRole(RolesAndPermissionsSeeder::ROLE_CONSULTA));
    }

    public function test_admin_can_deactivate_user(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $target = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_TECNICO);

        $this->actingAs($admin)
            ->delete(route('users.destroy', $target))
            ->assertRedirect(route('users.index'));

        $this->assertSame(UserStatus::Inactive, $target->fresh()->status);
    }

    public function test_non_admin_cannot_manage_users(): void
    {
        $tecnico = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_TECNICO);

        $this->actingAs($tecnico)
            ->get(route('users.index'))
            ->assertForbidden();

        $this->actingAs($tecnico)
            ->post(route('users.store'), [
                'name' => 'Hack',
                'email' => 'hack@example.com',
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
                'status' => UserStatus::Active->value,
                'role' => RolesAndPermissionsSeeder::ROLE_ADMIN,
            ])
            ->assertForbidden();
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);

        $this->actingAs($admin)
            ->delete(route('users.destroy', $admin))
            ->assertForbidden();

        $this->assertSame(UserStatus::Active, $admin->fresh()->status);
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
