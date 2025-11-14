<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Fortify view routes are disabled in config/fortify.php
        // We're using Livewire Volt routes defined in routes/auth.php instead

        // Two-factor authentication is disabled in config/fortify.php
        // Uncomment below if you enable 2FA later:
        // Fortify::twoFactorChallengeView(fn () => view('livewire.auth.two-factor-challenge'));
        // Fortify::confirmPasswordView(fn () => view('livewire.auth.confirm-password'));
        // RateLimiter::for('two-factor', function (Request $request) {
        //     return Limit::perMinute(5)->by($request->session()->get('login.id'));
        // });
    }
}
