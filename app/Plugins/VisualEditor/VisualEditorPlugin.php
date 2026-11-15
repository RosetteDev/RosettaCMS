<?php

namespace RosettaCMS\Plugins\VisualEditor;

use RosettaCMS\Plugin;
use Illuminate\Support\ServiceProvider;

class VisualEditorPlugin extends Plugin
{
    protected $name = 'Visual Editor';
    
    protected $description = 'A hybrid visual editor with both block-based and inline editing capabilities.';
    
    protected $version = '1.0.0';
    
    protected $author = 'Rosetta CMS Team';
    
    protected $homepage = 'https://github.com/RosettaDev/RosettaCMS';

    /**
     * Bootstrap the plugin.
     */
    public function bootstrap(): void
    {
        // Register service provider
        $this->app->register(VisualEditorServiceProvider::class);
        
        // Publish assets
        $this->publishAssets();
    }

    /**
     * Publish plugin assets.
     */
    protected function publishAssets(): void
    {
        $this->publishes([
            __DIR__.'/../Resources/assets' => public_path('plugins/visual-editor'),
        ], 'visual-editor-assets');
        
        $this->publishes([
            __DIR__.'/../Resources/views' => resource_path('views/vendor/visual-editor'),
        ], 'visual-editor-views');
    }
}
