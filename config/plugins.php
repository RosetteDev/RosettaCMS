<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Plugin Settings
    |--------------------------------------------------------------------------
    |
    | This file controls the plugin system for Rosetta CMS. You can enable,
    | disable, and configure plugins here.
    |
    */

    // Default plugin status
    'default_status' => 'disabled',

    // Plugin loading
    'load' => [
        // Core plugins - these should generally be enabled
        'VisualEditor' => [
            'enabled' => true,
            'priority' => 100,
        ],
        
        'SEO' => [
            'enabled' => true,
            'priority' => 200,
        ],
        
        'MultiSite' => [
            'enabled' => true,
            'priority' => 150,
        ],
        
        'WordPress' => [
            'enabled' => true,
            'priority' => 300,
        ],

        'Forms' => [
            'enabled' => true,
            'priority' => 250,
        ],
    ],

    // Plugin storage
    'storage' => [
        'path' => storage_path('app/plugins'),
        'cache' => [
            'enabled' => true,
            'ttl' => 3600, // Cache for 1 hour
        ],
    ],

    // Plugin permissions
    'permissions' => [
        // Ability to install plugins
        'install' => 'admin.plugins.install',
        
        // Ability to uninstall plugins
        'uninstall' => 'admin.plugins.uninstall',
        
        // Ability to enable/disable plugins
        'toggle' => 'admin.plugins.toggle',
        
        // Ability to configure plugins
        'configure' => 'admin.plugins.configure',
    ],

    // Plugin events
    'events' => [
        'plugin_installed' => \RosettaCMS\Events\PluginInstalled::class,
        'plugin_uninstalled' => \RosettaCMS\Events\PluginUninstalled::class,
        'plugin_enabled' => \RosettaCMS\Events\PluginEnabled::class,
        'plugin_disabled' => \RosettaCMS\Events\PluginDisabled::class,
    ],

    // Auto-loading plugins
    'autoload' => [
        // Plugins to always load
        'always' => [
            // 'PluginName' => true,
        ],
        
        // Plugins to never load automatically
        'exclude' => [
            // 'PluginName' => true,
        ],
    ],

];
