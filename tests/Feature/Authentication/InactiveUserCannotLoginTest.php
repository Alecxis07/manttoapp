<?php

namespace Tests\Feature\Authentication;

use App\Enums\UserStatus;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InactiveUserCannotLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_authenticate(): void
    {
        $user = User::factory()->create([
            'email' => 'active@example.com',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);

        $response = $this->post('/login', [
            'email' => 'active@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'login_success',
            'user_id' => $user->id,
        ]);
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        $user = User::factory()->inactive()->create([
            'email' => 'inactive@example.com',
            'password' => 'password',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'inactive@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $this->assertGuest();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'login_failed',
            'user_id' => $user->id,
        ]);

        $log = ActivityLog::query()->where('action', 'login_failed')->first();
        $this->assertSame('inactive', $log?->properties['reason'] ?? null);
    }

    public function test_inactive_authenticated_user_is_logged_out_on_next_request(): void
    {
        $user = User::factory()->create([
            'status' => UserStatus::Active,
        ]);

        $this->actingAs($user, 'web');

        $user->update(['status' => UserStatus::Inactive]);

        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest('web');
    }
}
