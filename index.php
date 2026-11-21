<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Check if vendor directory exists - if not, redirect to installer
if (!file_exists(__DIR__.'/../vendor/autoload.php')) {
    // Check if install directory exists
    if (file_exists(__DIR__.'/../app/Http/Controllers/InstallController.php')) {
        http_response_code(503);
        echo '<h1>Dependencies Not Installed</h1>';
        echo '<p>Please run <code>composer install --optimize-autoloader</code> first.</p>';
        echo '<p>Or visit <a href="/install/check">/install/check</a> to run the web installer.</p>';
        exit(1);
    }
    
    die('<h1>Dependencies Not Installed</h1>
    <p>Please run: <code>php composer.phar install --optimize-autoloader</code></p>');
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
