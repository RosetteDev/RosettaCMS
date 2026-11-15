<?php

namespace RosettaCMS\Plugins\WordPress;

use RosettaCMS\Plugin;

class WordPressCompatibilityPlugin extends Plugin
{
    protected $name = 'WordPress Compatibility';
    
    protected $description = 'Provides WordPress plugin API compatibility for Rosetta CMS.';
    
    protected $version = '1.0.0';
    
    protected $author = 'Rosetta CMS Team';
    
    protected $homepage = 'https://github.com/RosettaDev/RosettaCMS';

    /**
     * Bootstrap the plugin.
     */
    public function bootstrap(): void
    {
        // Register service provider
        $this->app->register(WordPressCompatibilityServiceProvider::class);
        
        // Include WordPress compatibility functions
        $this->includeFunctions();
    }

    /**
     * Include WordPress compatibility functions.
     */
    protected function includeFunctions(): void
    {
        require_once __DIR__.'/WordPressFunctions.php';
    }
}
