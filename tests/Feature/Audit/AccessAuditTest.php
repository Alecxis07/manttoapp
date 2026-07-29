<?php

namespace Tests\Feature\Audit;

use App\Models\ActivityLog;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_failed_login_with_invalid_credentials_is_audited(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $this->from('/login')->post('/login', [
            'email' => 'user@example.com',
            'password' => 'wrong-password',
        ])->assertRedirect('/login');

        $log = ActivityLog::query()->where('action', 'login_failed')->first();

        $this->assertNotNull($log);
        $this->assertSame('invalid_credentials', $log->properties['reason'] ?? null);
    }

    public function test_authorization_denial_is_audited(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole(RolesAndPermissionsSeeder::ROLE_CONSULTA);

        $this->actingAs($user)
            ->get(route('users.index'))
            ->assertForbidden();

        $log = ActivityLog::query()->where('action', 'authorization_denied')->first();

        $this->assertNotNull($log);
        $this->assertSame($user->id, $log->user_id);
        $this->assertSame('users.index', $log->properties['route'] ?? null);
    }

    public function test_user_management_changes_are_audited_via_auditable_trait(): void
    {
        $admin = User::factory()->withPersonalTeam()->create();
        $admin->assignRole(RolesAndPermissionsSeeder::ROLE_ADMIN);

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Auditado',
            'email' => 'auditado@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'status' => 'active',
            'role' => RolesAndPermissionsSeeder::ROLE_TECNICO,
        ])->assertRedirect(route('users.index'));

        $created = User::query()->where('email', 'auditado@example.com')->firstOrFail();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'created',
            'subject_type' => $created->getMorphClass(),
            'subject_id' => $created->id,
            'user_id' => $admin->id,
        ]);
    }
}
