<?php

namespace App\Providers;

use App\Http\Controllers\ImageOptimizer;
use Illuminate\Support\ServiceProvider;

class ImageOptimizerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('image.optimizer', function ($app) {
            return new class {
                use ImageOptimizer;
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register Intervention Image if not already registered
        if (!class_exists(\Intervention\Image\Facades\Image::class)) {
            // Could register the facade here if needed
        }
    }
}
