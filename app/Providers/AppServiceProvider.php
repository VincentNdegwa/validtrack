<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
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
        // Register the middleware
        $router = $this->app['router'];
        $router->aliasMiddleware('permission', \App\Http\Middleware\CheckPermission::class);
        $router->aliasMiddleware('super-admin', \App\Http\Middleware\SuperAdmin::class);
        $router->aliasMiddleware('company-context', \App\Http\Middleware\CompanyContext::class);
        
        Inertia::share([
            'flash' => function () {
                return [
                    'success' => Session::get('success'),
                    'error' => Session::get('error'),
                    'warning' => Session::get('warning'),
                    'info' => Session::get('info'),
                ];
            },
        ]);
    }
}
