<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CDN Enabled
    |--------------------------------------------------------------------------
    |
    | This option determines if CDN is enabled for media assets.
    |
    */

    'enabled' => env('CDN_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | CDN URL
    |--------------------------------------------------------------------------
    |
    | The base URL for your CDN. This should not include a trailing slash.
    | Example: https://cdn.example.com
    |
    */

    'url' => env('CDN_URL'),

    /*
    |--------------------------------------------------------------------------
    | CDN Disk
    |--------------------------------------------------------------------------
    |
    | The storage disk that contains your CDN assets.
    |
    */

    'disk' => env('CDN_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | CDN Assets Pattern
    |--------------------------------------------------------------------------
    |
    | Glob pattern for matching assets to be served from CDN.
    |
    */

    'pattern' => env('CDN_PATTERN', 'storage/**/*'),

    /*
    |--------------------------------------------------------------------------
    | CDN Cache Time
    |--------------------------------------------------------------------------
    |
    | Cache time for CDN URLs in seconds.
    |
    */

    'cache_time' => env('CDN_CACHE_TIME', 3600),

];
