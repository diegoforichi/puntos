<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Route::macro('tenantApi', function () {
            Route::prefix('{tenant}/api')
                ->middleware(['tenant', 'tenant.api'])
                ->group(function () {
                    require base_path('routes/tenant_api.php');
                });
        });
    }
}

