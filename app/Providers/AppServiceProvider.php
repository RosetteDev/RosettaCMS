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
}
