<?php

namespace App\Providers;
use Illuminate\Support\Facades\Cache;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{

    public function getData()
    {
        // Check if data is already cached
        $data = Cache::get('key');

        if (!$data) {
            // Simulate data retrieval (e.g., from a database)
            $data = 'fetched data'; // Replace with actual data source

            // Store data in cache
            Cache::put('key', $data, now()->addMinutes(10));
        }

        return $data;
    }
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // General rate limiting for the API
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    
        // Rate limiting specifically for login attempts
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip()); // 5 requests per minute per IP address
        });
    }
    
}
