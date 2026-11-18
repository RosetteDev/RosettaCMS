<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register HTMLPurifier service provider
        $this->app->register(\Mews\Purifier\PurifierServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default timezone
        date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));
        
        // Set default encoding
        mb_internal_encoding('UTF-8');
        
        // Register custom paths
        $this->registerPluginPaths();
    }
    
    /**
     * Register plugin paths.
     */
    protected function registerPluginPaths(): void
    {
        // Register plugin assets path
        $this->app['view']->addNamespace('plugins', app_path('Plugins'));
    }
}
