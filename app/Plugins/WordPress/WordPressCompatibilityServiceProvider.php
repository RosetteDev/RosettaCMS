<?php

namespace RosettaCMS\Plugins\WordPress;

use Illuminate\Support\ServiceProvider;

class WordPressCompatibilityServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register WordPress compatibility helper
        $this->app->singleton('wordpress.compat', function ($app) {
            return new WordPressCompatibility();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register WordPress-style actions and filters
        $this->registerActions();
        $this->registerFilters();
    }

    /**
     * Register WordPress-style actions.
     */
    protected function registerActions(): void
    {
        $this->app->make('wordpress.compat')->registerActions();
    }

    /**
     * Register WordPress-style filters.
     */
    protected function registerFilters(): void
    {
        $this->app->make('wordpress.compat')->registerFilters();
    }
}
