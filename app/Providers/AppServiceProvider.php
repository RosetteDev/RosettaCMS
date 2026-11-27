<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
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

        // Register content gates
        $this->registerGates();

        // Register custom paths
        $this->registerPluginPaths();

        // Register per-theme view namespaces (themes with a views/ directory)
        $this->registerThemeViews();
    }

    /**
     * Register content gates used by the inline editing and preview routes.
     */
    protected function registerGates(): void
    {
        Gate::define('inline_edit', function ($user) {
            return $user->hasRole('admin', 'editor');
        });

        Gate::define('edit', function ($user, $post) {
            return $user->hasRole('admin', 'editor', 'author')
                || $user->hasPermission('posts');
        });

        Gate::define('view', function ($user, $post) {
            return $user->hasRole('admin', 'editor', 'author')
                || $user->hasPermission('posts');
        });
    }

    /**
     * Register plugin paths.
     */
    protected function registerPluginPaths(): void
    {
        // Register plugin assets path
        $this->app['view']->addNamespace('plugins', app_path('Plugins'));
    }

    /**
     * Register a view namespace for each theme that ships its own views/
     * directory. This makes `themes.{slug}::{view}` resolve to the theme's
     * own templates (e.g. `themes.elegant::layouts.app`).
     */
    protected function registerThemeViews(): void
    {
        $themesDir = base_path('themes');

        if (! is_dir($themesDir)) {
            return;
        }

        foreach (glob($themesDir . '/*', GLOB_ONLYDIR) ?: [] as $themeDir) {
            $viewsDir = $themeDir . '/views';

            if (! is_dir($viewsDir)) {
                continue;
            }

            $slug = basename($themeDir);

            $this->app['view']->addNamespace('themes.' . $slug, $viewsDir);
        }
    }
}
