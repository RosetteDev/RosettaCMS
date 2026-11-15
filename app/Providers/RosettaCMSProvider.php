<?php

namespace RosettaCMS\Providers;

use Illuminate\Support\ServiceProvider;

class RosettaCMSProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register core services
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
        
        // Register plugin manager
        $this->app->singleton('rosetta.plugins', function ($app) {
            return new PluginManager($app);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        
        // Load seeders
        $this->loadSeedersFrom(__DIR__ . '/../Database/Seeders');
    }
}
