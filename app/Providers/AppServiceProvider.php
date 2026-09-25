<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once __DIR__.'/../helpers.php';
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enforce secure password complexity standards
        Password::defaults(function () {
            $rule = Password::min(8)->letters()->numbers();

            return app()->isProduction()
                ? $rule->mixedCase()->symbols()->uncompromised()
                : $rule;
        });

        // Layer 1: Named Rate Limiters for Brute-Force & Denial-of-Service Defense
        RateLimiter::for('login', function (Request $request) {
            $key = (string) ($request->input('email', '').'|'.$request->ip());

            return Limit::perMinute(5)->by($key)->response(function () {
                return back()->withErrors([
                    'email' => 'Too many login attempts. Please wait 60 seconds before trying again.',
                ])->onlyInput('email');
            });
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('portal-login', function (Request $request) {
            $key = (string) ($request->input('email', '').'|'.$request->ip());

            return Limit::perMinute(5)->by($key)->response(function () {
                return back()->withInput()->with('error', 'Too many portal login attempts. Please wait 60 seconds before trying again.');
            });
        });

        RateLimiter::for('magic-link', function (Request $request) {
            $key = (string) ($request->input('email', '').'|'.$request->ip());

            return Limit::perMinutes(5, 3)->by($key)->response(function () {
                return back()->with('error', 'Too many magic link requests. Please wait a few minutes before trying again.');
            });
        });

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('cron', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        RateLimiter::for('public-pay', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
