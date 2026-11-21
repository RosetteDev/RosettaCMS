<?php

namespace App\Providers;

use App\Http\Controllers\CDNHelper;
use Illuminate\Support\ServiceProvider;

class CDNServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('cdn', function ($app) {
            return new class {
                use CDNHelper;
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish CDN config
        $this->publishes([
            __DIR__.'/../../config/cdn.php' => config_path('cdn.php'),
        ], 'cdn-config');
    }
}
