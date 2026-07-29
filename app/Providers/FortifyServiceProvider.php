<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Enums\UserStatus;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        Fortify::authenticateUsing(function (Request $request) {
            /** @var ActivityLogger $logger */
            $logger = app(ActivityLogger::class);

            $user = User::query()
                ->where(Fortify::username(), $request->input(Fortify::username()))
                ->first();

            if (! $user || ! Hash::check((string) $request->password, $user->password)) {
                $logger->log(
                    action: 'login_failed',
                    subject: $user,
                    properties: [
                        'email' => $request->input(Fortify::username()),
                        'reason' => 'invalid_credentials',
                    ],
                    userId: $user?->id,
                );

                return null;
            }

            if ($user->status !== UserStatus::Active) {
                $logger->log(
                    action: 'login_failed',
                    subject: $user,
                    properties: [
                        'email' => $user->email,
                        'reason' => 'inactive',
                    ],
                    userId: $user->id,
                );

                return null;
            }

            return $user;
        });

        Event::listen(Login::class, function (Login $event): void {
            app(ActivityLogger::class)->log(
                action: 'login_success',
                subject: $event->user instanceof User ? $event->user : null,
                properties: [
                    'guard' => $event->guard,
                ],
                userId: $event->user->getAuthIdentifier(),
            );
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        // RNF-SEG-004 — password recovery: max 3 requests per hour per email+IP.
        RateLimiter::for('password-reset', function (Request $request) {
            $email = Str::transliterate(Str::lower((string) $request->input('email')));

            return Limit::perHour(3)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('passkeys', function (Request $request) {
            $credentialId = $request->input('credential.id');

            return Limit::perMinute(10)->by(
                ($credentialId ?: $request->session()->getId()).'|'.$request->ip()
            );
        });
    }
}
