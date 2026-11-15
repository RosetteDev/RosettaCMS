<?php

namespace RosettaCMS\Providers;

use Illuminate\Support\ServiceProvider;
use RosettaCMS\PluginManager;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('plugin.manager', function ($app) {
            return new PluginManager($app);
        });

        // Boot all registered plugins
        $this->bootPlugins();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish plugin assets
        $this->publishes([
            __DIR__.'/../../plugins' => public_path('plugins'),
        ], 'plugin-assets');
    }

    /**
     * Boot all registered plugins.
     */
    protected function bootPlugins(): void
    {
        $manager = $this->app->make('plugin.manager');
        $plugins = $manager->scan();

        foreach ($plugins as $name => $plugin) {
            // Check if plugin should be activated
            if ($this->shouldActivatePlugin($name)) {
                $manager->activate($name);
            }
        }
    }

    /**
     * Determine if a plugin should be activated.
     *
     * @param string $name
     * @return bool
     */
    protected function shouldActivatePlugin(string $name): bool
    {
        // Check if plugin is stored as active
        $activePlugins = config('plugins.active', []);
        return in_array($name, $activePlugins);
    }
}
