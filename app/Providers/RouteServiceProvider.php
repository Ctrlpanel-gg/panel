<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->name('api.')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('web', function (Request $request) {
            return Limit::perMinute(40)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('2fa.verify', function (Request $request) {
            $method = $request->route('method');
            $limit = app(\App\Services\TwoFactor\TwoFactorService::class)->getExtension($method)?->getRateLimit('verify')
                     ?? ['attempts' => 5, 'minutes' => 1];

            return Limit::perMinutes($limit['minutes'], $limit['attempts'])->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('2fa.setup', function (Request $request) {
            $method = $request->route('method');
            $limit = app(\App\Services\TwoFactor\TwoFactorService::class)->getExtension($method)?->getRateLimit('setup')
                     ?? ['attempts' => 3, 'minutes' => 1];

            return Limit::perMinutes($limit['minutes'], $limit['attempts'])->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('2fa.enable', function (Request $request) {
            $method = $request->route('method');
            $limit = app(\App\Services\TwoFactor\TwoFactorService::class)->getExtension($method)?->getRateLimit('enable')
                     ?? ['attempts' => 3, 'minutes' => 1];

            return Limit::perMinutes($limit['minutes'], $limit['attempts'])->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('2fa.disable', function (Request $request) {
            $method = $request->route('method');
            $limit = app(\App\Services\TwoFactor\TwoFactorService::class)->getExtension($method)?->getRateLimit('disable')
                     ?? ['attempts' => 3, 'minutes' => 1];

            return Limit::perMinutes($limit['minutes'], $limit['attempts'])->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('2fa.action', function (Request $request) {
            $method = $request->route('method');
            $limit = app(\App\Services\TwoFactor\TwoFactorService::class)->getExtension($method)?->getRateLimit('action')
                     ?? ['attempts' => 3, 'minutes' => 5];

            return Limit::perMinutes($limit['minutes'], $limit['attempts'])->by($request->user()?->id ?: $request->ip());
        });
    }
}
