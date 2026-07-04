<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Prevent lazy loading violations in production for clearer errors
        Model::preventLazyLoading(! app()->isProduction());

        // Force HTTPS in production (VPS behind reverse proxy)
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        // Rate limiters
        RateLimiter::for('inquiries', function (Request $request) {
            $perMinute = (int) config('services.inquiry_rate_limit_per_minute', 10);

            return Limit::perMinute($perMinute)->by($request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email').$request->ip());
        });
    }
}