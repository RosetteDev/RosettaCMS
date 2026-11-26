<?php

/**
 * Patch the vendored Laravel framework config to use the PHP 8.5-safe PDO constants.
 *
 * The vendored config/database.php ships with `PDO::MYSQL_ATTR_SSL_CA`, which is
 * deprecated since PHP 8.5 and emits a deprecation on every boot. Collision's own
 * fix-pdo-constant.php script resolves its target path incorrectly, so this script
 * performs the same replacement (idempotently) on install/update.
 */

$file = __DIR__.'/../vendor/laravel/framework/config/database.php';

if (! file_exists($file)) {
    exit(0);
}

$contents = file_get_contents($file);

if (str_contains($contents, 'Pdo\\Mysql::ATTR_SSL_CA')) {
    exit(0); // already patched
}

$patched = str_replace(
    'PDO::MYSQL_ATTR_SSL_CA',
    '(PHP_VERSION_ID >= 80500 ? Pdo\\Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA)',
    $contents,
);

file_put_contents($file, $patched);
