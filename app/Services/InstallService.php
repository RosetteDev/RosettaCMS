<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Role;
use App\Models\Site;
use App\Models\SiteSetting;
use App\Models\User;

/**
 * Shared install logic used by both the CLI installer (`php artisan install`)
 * and the web installer (/install). One code path: same preflight checks,
 * same result.
 *
 * Plain-language failures, never stack traces. All side effects (.env writes,
 * migrations, seeding) live behind install() — preflight() is read-only.
 */
class InstallService
{
    /** Minimum supported PHP version. */
    public const MIN_PHP = '8.2.0';

    /**
     * Run preflight checks. Read-only — safe to call before any writes.
     *
     * @return array{all_passed: bool, checks: array<string, array>, failures: array<int, string>}
     */
    public function preflight(?string $dbConnection = null, array $db = []): array
    {
        $dbConnection = $dbConnection ?: (string) env('DB_CONNECTION', 'sqlite');

        $checks = [
            'php_version' => $this->checkPhpVersion(),
            'extensions' => $this->checkExtensions($dbConnection),
            'directories' => $this->checkDirectories(),
            'dependencies' => $this->checkDependencies(),
            'database' => $this->checkDatabase($dbConnection, $db),
        ];

        $failures = [];
        foreach ($checks as $check) {
            if (!$check['passed']) {
                $failures[] = $check['message'];
            }
        }

        return [
            'all_passed' => empty($failures),
            'checks' => $checks,
            'failures' => $failures,
        ];
    }

    /**
     * Has this app already been installed? Reads runtime config and the .env
     * file — does not touch the database.
     */
    public function isInstalled(?string $envPath = null): bool
    {
        if (config('app.installed')) {
            return true;
        }

        $envPath = $envPath ?? base_path('.env');

        if (is_file($envPath)) {
            $content = file_get_contents($envPath);
            if (preg_match('/^APP_INSTALLED=["\']?(true|1|yes)["\']?\s*$/im', $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Run the install. All failures are returned as plain-language strings;
     * never thrown with stack traces.
     *
     * @param array{
     *   site_name?: string, app_url?: string, admin_email?: string,
     *   admin_password?: string, db_connection?: string, db_host?: string|null,
     *   db_port?: int|string|null, db_database?: string|null,
     *   db_username?: string|null, db_password?: string|null,
     *   force?: bool, env_path?: string|null, skip_config_clear?: bool
     * } $options
     * @return array{success: bool, errors: array<int, string>, warnings: array<int, string>}
     */
    public function install(array $options): array
    {
        $envPath = $options['env_path'] ?? base_path('.env');
        $force = (bool) ($options['force'] ?? false);

        if (!$force && $this->isInstalled($envPath)) {
            return [
                'success' => false,
                'errors' => ['Rosetta CMS is already installed. Pass --force to reinstall over it.'],
                'warnings' => [],
            ];
        }

        try {
            $siteName = trim((string) ($options['site_name'] ?? ''));
            $appUrl = trim((string) ($options['app_url'] ?? ''));
            $adminEmail = trim((string) ($options['admin_email'] ?? ''));
            $adminPassword = (string) ($options['admin_password'] ?? '');

            $errors = [];

            if ($siteName === '') {
                $errors[] = 'A site name is required.';
            }
            if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'A valid admin email is required.';
            }
            if (strlen($adminPassword) < 8) {
                $errors[] = 'The admin password must be at least 8 characters.';
            }
            if ($appUrl === '') {
                $appUrl = 'http://localhost:8000';
            }

            if ($errors) {
                return ['success' => false, 'errors' => $errors, 'warnings' => []];
            }

            $dbConnection = $options['db_connection'] ?? 'sqlite';
            $warnings = [];

            // Apply database config in-process before anything touches it.
            $this->applyDatabaseConfig($dbConnection, $options);

            // Refuse to install over an existing application (tables exist).
            try {
                $tablesExist = false;

                if ($dbConnection === 'sqlite') {
                    $dbPath = $options['db_database'] ?? database_path('database.sqlite');
                    // A missing sqlite file means a fresh install — no tables yet.
                    if (file_exists($dbPath)) {
                        $tablesExist = Schema::hasTable('users') || Schema::hasTable('migrations');
                    }
                } else {
                    $tablesExist = Schema::hasTable('users') || Schema::hasTable('migrations');
                }

                if ($tablesExist && !$force) {
                    return [
                        'success' => false,
                        'errors' => ['The selected database already contains Rosetta CMS tables. Use an empty database, or pass --force to reinstall.'],
                        'warnings' => [],
                    ];
                }

                if ($tablesExist) {
                    $warnings[] = 'Database already contains tables — reinstalling in place.';
                }
            } catch (\Throwable $e) {
                return ['success' => false, 'errors' => ['Could not check the database: ' . $this->plainMessage($e)], 'warnings' => []];
            }

            // For MySQL, try to create the database (best effort).
            if ($dbConnection === 'mysql') {
                try {
                    $this->createMysqlDatabase($options);
                } catch (\Throwable $e) {
                    $warnings[] = 'Could not auto-create the database (may already exist or need manual creation): ' . $this->plainMessage($e);
                }
            }

            // Ensure database connectivity before migrating.
            // A missing sqlite file is fine — `migrate` creates it.
            $sqliteFile = $dbConnection === 'sqlite' ? ($options['db_database'] ?? database_path('database.sqlite')) : null;
            $skipConnectCheck = $dbConnection === 'sqlite' && !file_exists($sqliteFile);

            if (!$skipConnectCheck) {
                try {
                    DB::connection($dbConnection === 'sqlite' ? null : $dbConnection)->getPdo();
                } catch (\Throwable $e) {
                    return ['success' => false, 'errors' => ['Could not connect to the database: ' . $this->plainMessage($e)], 'warnings' => $warnings];
                }
            }

            // Ensure .env exists and generate an app key if missing.
            $envContent = $this->ensureEnvFile($envPath);
            $appKey = $this->readEnvValue($envContent, 'APP_KEY');
            if (!$appKey) {
                $appKey = 'base64:' . base64_encode(random_bytes(32));
            }

            // Run migrations.
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
                return ['success' => false, 'errors' => ['Migration failed: ' . $this->plainMessage($e)], 'warnings' => $warnings];
            }

            // Seed: site + settings + roles + admin user.
            try {
                $this->seed($siteName, $appUrl, $adminEmail, $adminPassword);
            } catch (\Throwable $e) {
                return ['success' => false, 'errors' => ['Failed to create the admin user or site: ' . $this->plainMessage($e)], 'warnings' => $warnings];
            }

            // Write .env last: only on a fully successful install.
            $this->writeEnv($envPath, [
                'APP_NAME' => $siteName,
                'APP_URL' => $appUrl,
                'APP_KEY' => $appKey,
                'DB_CONNECTION' => $dbConnection,
                'DB_HOST' => $options['db_host'] ?? '127.0.0.1',
                'DB_PORT' => $options['db_port'] ?? null,
                'DB_DATABASE' => $options['db_database'] ?? null,
                'DB_USERNAME' => $options['db_username'] ?? null,
                'DB_PASSWORD' => $options['db_password'] ?? null,
                'INSTALL_ENABLED' => 'false',
                'APP_INSTALLED' => 'true',
            ]);

            // Clear cached config so the new .env is picked up.
            if (!($options['skip_config_clear'] ?? false)) {
                try {
                    Artisan::call('config:clear');
                } catch (\Throwable $e) {
                    // Non-critical.
                }
            }

            return ['success' => true, 'errors' => [], 'warnings' => $warnings];
        } catch (\Throwable $e) {
            return ['success' => false, 'errors' => [$this->plainMessage($e)], 'warnings' => []];
        }
    }

    // ------------------------------------------------------------------
    // Preflight checks
    // ------------------------------------------------------------------

    private function checkPhpVersion(): array
    {
        $version = phpversion();
        $passed = version_compare($version, self::MIN_PHP, '>=');

        return [
            'name' => 'PHP version',
            'passed' => $passed,
            'message' => $passed
                ? "PHP {$version} is supported"
                : "PHP {$version} is too old — Rosetta CMS requires PHP " . self::MIN_PHP . ' or newer',
            'required' => self::MIN_PHP,
            'current' => $version,
        ];
    }

    private function checkExtensions(string $dbConnection): array
    {
        $required = [
            'openssl' => 'OpenSSL',
            'pdo' => 'PDO',
            'mbstring' => 'MBString',
            'tokenizer' => 'Tokenizer',
            'xml' => 'XML',
            'ctype' => 'Ctype',
            'fileinfo' => 'Fileinfo',
            'zip' => 'Zip',
        ];

        // Database driver for the chosen connection.
        $driverMap = [
            'sqlite' => 'pdo_sqlite',
            'mysql' => 'pdo_mysql',
            'pgsql' => 'pdo_pgsql',
            'sqlsrv' => 'pdo_sqlsrv',
        ];
        if (isset($driverMap[$dbConnection])) {
            $required[$driverMap[$dbConnection]] = strtoupper(str_replace('pdo_', '', $driverMap[$dbConnection])) . ' driver';
        }

        $missing = [];
        $installed = [];
        foreach ($required as $ext => $label) {
            if (extension_loaded($ext)) {
                $installed[] = $label;
            } else {
                $missing[] = $label;
            }
        }

        $passed = empty($missing);

        return [
            'name' => 'PHP extensions',
            'passed' => $passed,
            'message' => $passed
                ? 'All required PHP extensions are loaded'
                : 'Missing PHP extensions: ' . implode(', ', $missing),
            'installed' => $installed,
            'missing' => $missing,
        ];
    }

    private function checkDirectories(): array
    {
        $directories = [
            'storage' => storage_path(),
            'storage/app' => storage_path('app'),
            'storage/framework' => storage_path('framework'),
            'storage/framework/cache' => storage_path('framework/cache'),
            'storage/framework/sessions' => storage_path('framework/sessions'),
            'storage/framework/views' => storage_path('framework/views'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        $unwritable = [];
        foreach ($directories as $name => $path) {
            if (!is_dir($path) || !is_writable($path)) {
                $unwritable[] = $name;
            }
        }

        $passed = empty($unwritable);

        return [
            'name' => 'Directory permissions',
            'passed' => $passed,
            'message' => $passed
                ? 'All required directories are writable'
                : 'Not writable: ' . implode(', ', $unwritable) . ' — run "chmod -R 775 storage bootstrap/cache" or fix ownership',
            'directories' => $directories,
            'unwritable' => $unwritable,
        ];
    }

    private function checkDependencies(): array
    {
        $autoload = base_path('vendor/autoload.php');
        $passed = is_file($autoload);

        return [
            'name' => 'Dependencies',
            'passed' => $passed,
            'message' => $passed
                ? 'Composer dependencies are installed'
                : 'Dependencies are missing — run "composer install"',
        ];
    }

    private function checkDatabase(string $dbConnection, array $db): array
    {
        if ($dbConnection === 'sqlite') {
            $dbPath = $db['database'] ?? env('DB_DATABASE', database_path('database.sqlite'));
            $parent = dirname($dbPath);
            $passed = is_dir($parent) && is_writable($parent);

            return [
                'name' => 'Database',
                'passed' => $passed,
                'message' => $passed
                    ? "SQLite database directory is writable ({$dbPath})"
                    : "SQLite database directory is not writable: {$parent}",
            ];
        }

        // For non-sqlite drivers, actually try to connect when credentials are known.
        $host = $db['host'] ?? env('DB_HOST', '127.0.0.1');
        $port = $db['port'] ?? env('DB_PORT', '3306');
        $database = $db['database'] ?? env('DB_DATABASE');
        $username = $db['username'] ?? env('DB_USERNAME');
        $password = $db['password'] ?? env('DB_PASSWORD', '');

        if (!$database || !$username) {
            return [
                'name' => 'Database',
                'passed' => true,
                'message' => 'Database connectivity will be tested during install',
            ];
        }

        $passed = false;
        $reason = '';
        try {
            $pdo = new \PDO(
                sprintf('%s:host=%s;port=%s;dbname=%s', $this->pdoDriver($dbConnection), $host, $port, $database),
                $username,
                $password,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_TIMEOUT => 5]
            );
            $pdo->query('SELECT 1');
            $passed = true;
        } catch (\Throwable $e) {
            $reason = $this->plainMessage($e);
        }

        return [
            'name' => 'Database',
            'passed' => $passed,
            'message' => $passed
                ? "Connected to the {$dbConnection} database"
                : "Could not connect to the {$dbConnection} database ({$host}): " . $reason,
        ];
    }

    // ------------------------------------------------------------------
    // Internals
    // ------------------------------------------------------------------

    private function applyDatabaseConfig(string $dbConnection, array $options): void
    {
        config(['database.default' => $dbConnection]);

        if ($dbConnection === 'sqlite') {
            // SQLite needs no host/user — just a writable database path.
            config(['database.connections.sqlite.database' => $options['db_database'] ?? database_path('database.sqlite')]);
            DB::purge('sqlite');
            return;
        }

        $host = $options['db_host'] ?? env('DB_HOST', '127.0.0.1');
        $port = $options['db_port'] ?? env('DB_PORT', '3306');
        $database = $options['db_database'] ?? env('DB_DATABASE');
        $username = $options['db_username'] ?? env('DB_USERNAME');
        $password = $options['db_password'] ?? env('DB_PASSWORD', '');

        config([
            "database.connections.{$dbConnection}.host" => $host,
            "database.connections.{$dbConnection}.port" => $port,
            "database.connections.{$dbConnection}.database" => $database,
            "database.connections.{$dbConnection}.username" => $username,
            "database.connections.{$dbConnection}.password" => $password,
        ]);

        DB::purge($dbConnection);
        DB::reconnect($dbConnection);
    }

    private function createMysqlDatabase(array $options): void
    {
        $host = $options['db_host'] ?? '127.0.0.1';
        $port = $options['db_port'] ?? 3306;
        $database = $options['db_database'] ?? '';
        $username = $options['db_username'] ?? 'root';
        $password = $options['db_password'] ?? '';

        if ($database === '') {
            return;
        }

        $serverConnection = new \PDO(
            "mysql:host={$host};port={$port}",
            $username,
            $password,
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );

        $safeName = str_replace('`', '``', $database);
        $serverConnection->exec("CREATE DATABASE IF NOT EXISTS `{$safeName}`");
    }

    private function ensureEnvFile(string $envPath): string
    {
        if (is_file($envPath)) {
            return file_get_contents($envPath);
        }

        $example = base_path('.env.example');
        $content = is_file($example) ? file_get_contents($example) : "APP_NAME=Rosetta CMS\n";
        $this->writeEnvFile($envPath, $content);

        return $content;
    }

    private function readEnvValue(string $content, string $key): ?string
    {
        if (preg_match("/^{$key}=[\"']?(.*?)[\"']?\s*$/m", $content, $m)) {
            return $m[1];
        }
        return null;
    }

    private function writeEnvFile(string $path, string $content): void
    {
        file_put_contents($path, $content);
    }

    private function writeEnv(string $path, array $values): void
    {
        $content = is_file($path) ? file_get_contents($path) : '';

        $dbConnection = $values['DB_CONNECTION'] ?? 'sqlite';

        foreach ($values as $key => $value) {
            if ($value === null) {
                continue;
            }
            if ($dbConnection === 'sqlite' && in_array($key, ['DB_HOST', 'DB_PORT', 'DB_USERNAME', 'DB_PASSWORD'], true)) {
                // SQLite needs no host/user/password — leave them out entirely.
                continue;
            }

            $value = str_replace('"', '\\"', (string) $value);
            if (preg_match("/^{$key}=.*/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}=\"{$value}\"", $content);
            } else {
                $trimmed = rtrim($content);
                $content = $trimmed === '' ? "{$key}=\"{$value}\"\n" : $trimmed . "\n{$key}=\"{$value}\"\n";
            }
        }

        $this->writeEnvFile($path, $content);
    }

    private function seed(string $siteName, string $appUrl, string $adminEmail, string $adminPassword): void
    {
        // Site
        $site = Site::firstOrCreate(
            ['name' => $siteName],
            [
                'domain' => null,
                'description' => 'Just another Rosetta CMS site',
                'timezone' => 'UTC',
                'locale' => 'en',
                'currency' => 'USD',
                'is_active' => true,
            ]
        );

        // Settings
        SiteSetting::firstOrCreate(
            ['site_id' => $site->id],
            [
                'site_title' => $siteName,
                'site_description' => 'Just another Rosetta CMS site',
                'admin_email' => $adminEmail,
                'post_per_page' => 10,
            ]
        );

        // Admin role
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Full access to all features',
                'permissions' => [
                    'dashboard' => true,
                    'posts' => true,
                    'pages' => true,
                    'media' => true,
                    'users' => true,
                    'settings' => true,
                    'plugins' => true,
                    'themes' => true,
                    'seo' => true,
                    'multi_site' => true,
                ],
            ]
        );

        // Admin user
        $user = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin',
                'password' => Hash::make($adminPassword),
                'email_verified_at' => now(),
            ]
        );

        // Ensure the role is attached.
        $user->roles()->syncWithoutDetaching([$adminRole->id]);
    }

    private function pdoDriver(string $dbConnection): string
    {
        return match ($dbConnection) {
            'pgsql' => 'pgsql',
            'sqlsrv' => 'sqlsrv',
            default => 'mysql',
        };
    }

    /**
     * Turn a throwable into a plain-language message — no stack traces.
     */
    private function plainMessage(\Throwable $e): string
    {
        $message = trim($e->getMessage());

        // PDO messages are often verbose; keep them but never leak credentials.
        $message = preg_replace('/(password|pass|pwd)\s*=\s*[^\s;]+/i', '$1=***', $message);

        return $message !== '' ? $message : 'An unexpected error occurred.';
    }
}
