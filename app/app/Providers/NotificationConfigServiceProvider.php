<?php

namespace App\Providers;

use App\Services\NotificationConfigResolver;
use Illuminate\Support\ServiceProvider;

class NotificationConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(NotificationConfigResolver::class, function () {
            return new NotificationConfigResolver();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
