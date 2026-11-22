<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Check if vendor directory exists - if not, show dependency error
if (!file_exists(__DIR__.'/vendor/autoload.php')) {
    http_response_code(503);
    echo '<h1>Dependencies Not Installed</h1>';
    echo '<p>Run these commands on your server (one at a time):</p>';
    echo '<ol>';
    echo '<li><code>php -r "copy(\'https://getcomposer.org/installer\', \'composer-setup.php\');"</code></li>';
    echo '<li><code>php composer-setup.php</code></li>';
    echo '<li><code>php -r "unlink(\'composer-setup.php\');"</code></li>';
    echo '<li><code>php -d detect_unicode=0 composer.phar install --optimize-autoloader --no-dev</code></li>';
    echo '</ol>';
    echo '<p>Or visit <a href="/install/check">/install/check</a> to run the web installer.</p>';
    exit(1);
}

// Register the Composer autoloader...
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request
$app = require_once __DIR__.'/bootstrap/app.php';

// Handle the request and send the response
$request = Request::capture();
$response = $app->handle($request);
$response->send();
$app->terminate($request, $response);
