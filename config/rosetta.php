<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rosetta CMS Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the default settings for Rosetta CMS.
    |
    */

    /**
     * Enable inline editing on public pages.
     * When enabled, admins can edit page content directly on the frontend.
     */
    'inline_edit_enabled' => env('ROSETTA_INLINE_EDIT_ENABLED', false),

    /**
     * Default theme settings.
     */
    'theme' => [
        'default' => 'rosetta',
        'cdn_enabled' => env('ROSETTA_CDN_ENABLED', true),
    ],

    /**
     * Block editor settings.
     */
    'blocks' => [
        'max_blocks_per_page' => 50,
        'allowed_types' => [
            'text',
            'heading',
            'image',
            'gallery',
            'callout',
            'grid',
            'quote',
            'divider',
            'html',
        ],
    ],

    /**
     * Cache settings.
     */
    'cache' => [
        'enabled' => env('ROSETTA_CACHE_ENABLED', true),
        'ttl' => env('ROSETTA_CACHE_TTL', 3600),
    ],
];
