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
        
        // Share permissions with every view for authorization in the frontend
        Inertia::share([
            'auth' => function () {
                $user = Auth::user();
                
                if (!$user) {
                    return null;
                }
                
                // Use the getAllPermissions method to get permissions
                $permissions = $user->getAllPermissions();
                
                return [
                    'user' => array_merge($user->only('id', 'name', 'email'), [
                        'roles' => $user->roles,
                        'permissions' => $permissions,
                    ]),
                ];
            },
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
