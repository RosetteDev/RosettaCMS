<?php

namespace RosettaCMS\Plugins\MultiSite;

use RosettaCMS\Plugin;

class MultiSitePlugin extends Plugin
{
    protected $name = 'Multi-Site Manager';
    
    protected $description = 'Manage multiple websites from a single Rosetta CMS installation.';
    
    protected $version = '1.0.0';
    
    protected $author = 'Rosetta CMS Team';
    
    protected $homepage = 'https://github.com/RosettaDev/RosettaCMS';

    /**
     * Bootstrap the plugin.
     */
    public function bootstrap(): void
    {
        // Register service provider
        $this->app->register(MultiSiteServiceProvider::class);
        
        // Register middleware
        $this->registerMiddleware();
    }

    /**
     * Register middleware.
     */
    protected function registerMiddleware(): void
    {
        // Register current site middleware
        $this->app['router']->aliasMiddleware('site', \RosettaCMS\Plugins\MultiSite\Middleware\CurrentSite::class);
    }
}
