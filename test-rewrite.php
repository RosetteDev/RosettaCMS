<?php

// Simple test to verify if Apache is processing .htaccess
echo "<h1>Apache Rewrite Test</h1>";
echo "<p>This file is at: " . __FILE__ . "</p>";
echo "<p>Current URI: " . ($_SERVER['REQUEST_URI'] ?? 'unknown') . "</p>";

// Check if mod_rewrite is enabled
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo "<h2>Loaded Apache Modules:</h2>";
    echo "<pre>" . print_r($modules, true) . "</pre>";
    
    if (in_array('mod_rewrite', $modules)) {
        echo "<p><strong>mod_rewrite is ENABLED</strong></p>";
    } else {
        echo "<p><strong>mod_rewrite is NOT ENABLED</strong></p>";
    }
} else {
    echo "<p>Could not determine loaded modules</p>";
}

// Show server variables related to rewrite
echo "<h2>Server Variables:</h2>";
echo "<pre>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'not set') . "\n";
echo "REDIRECT_URL: " . ($_SERVER['REDIRECT_URL'] ?? 'not set') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'not set') . "\n";
echo "PATH_INFO: " . ($_SERVER['PATH_INFO'] ?? 'not set') . "\n";
echo "</pre>";

?>
