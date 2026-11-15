<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SEO Settings
    |--------------------------------------------------------------------------
    |
    | These settings control the default SEO behavior for your Rosetta CMS
    | installation. You can configure meta tags, social media sharing,
    | and search engine optimization options here.
    |
    */

    // Default SEO settings
    'default' => [
        'title' => env('APP_NAME', 'Rosetta CMS'),
        'title_separator' => '|',
        'title_append' => '',
        'description' => 'A modern, flexible content management system built with Laravel',
        'keywords' => 'cms, content management, laravel, blog, website',
        'author' => 'Rosetta CMS',
        'robots' => 'index, follow',
        'canonical' => null,
        'og_type' => 'website',
        'og_image' => null,
        'twitter_card' => 'summary_large_image',
    ],

    // Social media settings
    'social' => [
        'facebook_app_id' => env('FB_APP_ID'),
        'twitter_handle' => env('TWITTER_HANDLE'),
        'linkedin_publisher' => env('LINKEDIN_PUBLISHER'),
        'instagram_username' => env('INSTAGRAM_USERNAME'),
        'youtube_channel' => env('YOUTUBE_CHANNEL'),
    ],

    // XML Sitemap settings
    'sitemap' => [
        'enabled' => true,
        'filename' => 'sitemap.xml',
        'path' => storage_path('app/sitemap'),
        'changefreq' => [
            'homepage' => 'daily',
            'posts' => 'weekly',
            'pages' => 'monthly',
        ],
        'priority' => [
            'homepage' => 1.0,
            'posts' => 0.8,
            'pages' => 0.7,
        ],
    ],

    // Open Graph settings
    'og' => [
        'default_image' => null,
        'default_image_width' => 1200,
        'default_image_height' => 630,
        'default_type' => 'website',
    ],

    // Google Analytics settings
    'analytics' => [
        'enabled' => env('GOOGLE_ANALYTICS_ENABLED', false),
        'tracking_id' => env('GOOGLE_ANALYTICS_ID'),
        'anonymize_ips' => true,
    ],

    // Breadcrumbs settings
    'breadcrumbs' => [
        'enabled' => true,
        'separator' => '>',
        'home_label' => 'Home',
    ],

    // Structured data
    'structured_data' => [
        'enabled' => true,
        'organization' => [
            'name' => env('APP_NAME', 'Rosetta CMS'),
            'url' => env('APP_URL', 'https://localhost'),
            'logo' => null,
            'same_as' => [],
        ],
    ],

];
