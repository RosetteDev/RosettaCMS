<?php

namespace RosettaCMS\Providers;

use Illuminate\Support\ServiceProvider;
use RosettaCMS\PluginManager;

class RosettaCMSProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('plugin.manager', function ($app) {
            return new PluginManager($app);
        });

        $this->app->singleton(PluginManager::class, function ($app) {
            return $app['plugin.manager'];
        });

        $this->app->alias('plugin.manager', 'rosetta.plugins');
    }
}
