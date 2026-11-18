<?php

namespace RosettaCMS\Providers;

use Illuminate\Support\ServiceProvider;
use RosettaCMS\Plugin;
use RosettaCMS\PluginManager;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        if (!$this->app->bound('plugin.manager')) {
            $this->app->singleton('plugin.manager', function ($app) {
                return new PluginManager($app);
            });
        }

        if (!$this->app->bound('rosetta.plugins')) {
            $this->app->alias('plugin.manager', 'rosetta.plugins');
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->bootPlugins();

        // Publish plugin assets
        if (is_dir(base_path('plugins'))) {
            $this->publishes([
                base_path('plugins') => public_path('plugins'),
            ], 'plugin-assets');
        }
    }

    /**
     * Boot all plugins enabled in config/plugins.php.
     */
    protected function bootPlugins(): void
    {
        $manager = $this->app->make('plugin.manager');

        foreach ($manager->scan() as $name => $plugin) {
            if ($this->shouldActivatePlugin($plugin)) {
                $manager->activate($name);
            }
        }
    }

    /**
     * Determine if a plugin should be activated.
     */
    protected function shouldActivatePlugin(Plugin $plugin): bool
    {
        $key = $plugin->getKey();
        $active = config('plugins.active', []);

        if (is_array($active)) {
            if (in_array($key, $active, true) || in_array($plugin->getName(), $active, true)) {
                return true;
            }
        }

        return (bool) config("plugins.load.{$key}.enabled", false);
    }
}
