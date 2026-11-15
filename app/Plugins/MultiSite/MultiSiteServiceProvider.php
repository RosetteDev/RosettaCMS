<?php

namespace RosettaCMS\Plugins\MultiSite;

use Illuminate\Support\ServiceProvider;

class MultiSiteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the multi-site service
        $this->app->singleton('sites.manager', function ($app) {
            return new MultiSiteManager();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish migrations
        $this->publishes([
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ], 'multisite-migrations');
        
        // Publish seeders
        $this->publishes([
            __DIR__.'/../../database/seeders' => database_path('seeders'),
        ], 'multisite-seeders');
    }
}
