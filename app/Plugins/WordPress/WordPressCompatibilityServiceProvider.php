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
        // Register WordPress hooks
        $this->app->make('wordpress.compat')->hooks()->registerWordPressHooks();
    }
}
