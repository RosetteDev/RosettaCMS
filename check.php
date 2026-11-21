<?php

/**
 * Rosetta CMS - Server Checker
 * This file helps diagnose deployment issues
 */

echo "<h1>Server Configuration Check</h1>";
echo "<p>Checking deployment status...</p>";

// Check if we're in the right directory
echo "<h2>Directory Check</h2>";
echo "<p>Current directory: " . getcwd() . "</p>";

// Check for required files
echo "<h2>Required Files</h2>";
$required_files = [
    'index.php' => 'Root index.php',
    'artisan' => 'Laravel artisan',
    'composer.json' => 'Composer dependencies',
    'bootstrap/app.php' => 'Laravel bootstrap',
    'config/app.php' => 'Configuration',
];

echo "<ul>";
foreach ($required_files as $file => $desc) {
    $exists = file_exists($file);
    echo "<li style='color: " . ($exists ? 'green' : 'red') . "'>" . $desc . ": " . ($exists ? "✓ Found" : "✗ Missing") . "</li>";
}
echo "</ul>";

// Check PHP version
echo "<h2>PHP Configuration</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// Check required extensions
echo "<h2>Required Extensions</h2>";
$extensions = ['mysqli', 'pdo_mysql', 'curl', 'mbstring', 'json', 'xml', 'tokenizer', 'fileinfo'];
echo "<ul>";
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "<li style='color: " . ($loaded ? 'green' : 'red') . "'>" . $ext . ": " . ($loaded ? "✓ Loaded" : "✗ Missing") . "</li>";
}
echo "</ul>";

// Check writable directories
echo "<h2>Writable Directories</h2>";
$writable = ['storage', 'bootstrap/cache'];
echo "<ul>";
foreach ($writable as $dir) {
    $writable_check = is_writable($dir);
    echo "<li style='color: " . ($writable_check ? 'green' : 'red') . "'>" . $dir . ": " . ($writable_check ? "✓ Writable" : "✗ Not Writable") . "</li>";
}
echo "</ul>";

// Check if index.php is being served
echo "<h2>Entry Point Check</h2>";
echo "<p>If you can see this page, index.php is being executed.</p>";

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>If files are missing, re-upload the deployment package</li>";
echo "<li>If directories aren't writable: <code>chmod -R 775 storage bootstrap/cache</code></li>";
echo "<li>If extensions are missing, contact your hosting provider</li>";
echo "<li>If this page shows 403, check file permissions on this file</li>";
echo "</ol>";

echo "<p>Visit <a href='/'>Rosetta CMS</a> to continue setup.</p>";
