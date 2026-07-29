<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_rate_limited_after_five_attempts_per_minute(): void
    {
        User::factory()->create([
            'email' => 'limited@example.com',
            'password' => 'password',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->from('/login')->post('/login', [
                'email' => 'limited@example.com',
                'password' => 'wrong-password',
            ])->assertRedirect('/login');
        }

        $this->from('/login')->post('/login', [
            'email' => 'limited@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }

    public function test_password_recovery_is_rate_limited_after_three_requests_per_hour(): void
    {
        Notification::fake();
        config(['auth.passwords.users.throttle' => 0]);

        $user = User::factory()->create([
            'email' => 'reset@example.com',
        ]);

        for ($i = 0; $i < 3; $i++) {
            $this->from(route('password.request'))
                ->post(route('password.email'), [
                    'email' => $user->email,
                ])
                ->assertRedirect();
        }

        Notification::assertSentToTimes($user, ResetPassword::class, 3);

        $this->from(route('password.request'))
            ->post(route('password.email'), [
                'email' => $user->email,
            ])
            ->assertStatus(429);

        Notification::assertSentToTimes($user, ResetPassword::class, 3);
    }
}
