<?php

define('LARAVEL_START', microtime(true));

// Register the Composer autoloader...
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request
$app = require_once __DIR__.'/bootstrap/app.php';

// Check if the application can be booted
try {
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    echo "Laravel application booted successfully!\n";
    echo "Application version: " . $app->version() . "\n";
    
    // Check routes
    $routes = $app->make('router')->getRoutes();
    echo "Total routes registered: " . count($routes) . "\n";
    
    // Check for install routes specifically
    foreach ($routes as $route) {
        if (str_contains($route->uri, 'install')) {
            echo "Found install route: " . $route->uri . " (" . implode(', ', $route->methods) . ")\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
