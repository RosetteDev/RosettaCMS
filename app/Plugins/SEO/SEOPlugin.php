<?php

namespace RosettaCMS\Plugins\SEO;

use RosettaCMS\Plugin;

class SEOPlugin extends Plugin
{
    protected $name = 'SEO Tools';
    
    protected $description = 'Built-in SEO optimization features for Rosetta CMS.';
    
    protected $version = '1.0.0';
    
    protected $author = 'Rosetta CMS Team';
    
    protected $homepage = 'https://github.com/RosettaDev/RosettaCMS';

    /**
     * Bootstrap the plugin.
     */
    public function bootstrap(): void
    {
        // Register service provider
        $this->app->register(SEOServiceProvider::class);
        
        // Register helpers
        $this->registerHelpers();
    }

    /**
     * Register plugin helpers.
     */
    protected function registerHelpers(): void
    {
        // Register SEO helper
        $this->app->singleton('seo.helper', function ($app) {
            return new SEOHelper();
        });
    }
}
