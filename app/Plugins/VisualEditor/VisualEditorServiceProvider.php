<?php

namespace RosettaCMS\Plugins\VisualEditor;

use Illuminate\Support\ServiceProvider;

class VisualEditorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the visual editor service
        $this->app->singleton('visual.editor', function ($app) {
            return new VisualEditorService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish assets and views
        $this->publishes([
            __DIR__.'/../../Resources/assets' => public_path('plugins/visual-editor'),
        ], 'visual-editor-assets');
        
        $this->publishes([
            __DIR__.'/../../Resources/views' => resource_path('views/vendor/visual-editor'),
        ], 'visual-editor-views');
    }
}
