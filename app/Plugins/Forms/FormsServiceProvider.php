<?php

namespace RosettaCMS\Plugins\Forms;

use Illuminate\Support\ServiceProvider;

class FormsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('forms.service', function () {
            return new FormsService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Nothing to publish; the plugin owns its admin routes.
    }
}
