<?php

namespace RosettaCMS\Plugins\VisualEditor;

use RosettaCMS\Plugin;

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
    }
}
