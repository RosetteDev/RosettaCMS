<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PDO;

class InstallController extends Controller
{
    /**
     * Show the installation check page.
     */
    public function check(Request $request)
    {
        $checks = $this->runInstallationChecks();

        return view('install.check', compact('checks'));
    }

    /**
     * Show the installation form.
     */
    public function form(Request $request)
    {
        $checks = $this->runInstallationChecks();
        
        if (!$checks['all_passed']) {
            return redirect()->route('install.check')->withErrors(['message' => 'Please fix the issues above before continuing.']);
        }

        return view('install.form');
    }

    /**
     * Process the installation.
     */
    public function process(Request $request)
    {
        // Installation must be explicitly enabled by the server administrator.
        abort_unless(config('app.install_enabled', false), 403);
        $validated = $request->validate([
            'db_connection' => 'required|in:mysql,pgsql,sqlite,sqlsrv',
            'db_host' => 'required_if:db_connection,mysql|nullable|regex:/^[A-Za-z0-9_.\-]+$/|max:255',
            'db_port' => 'nullable|numeric|min:1|max:65535',
            'db_database' => 'required|regex:/^[A-Za-z0-9_\-]+$/|max:64',
            'db_username' => 'required|string|max:64',
            'db_password' => 'nullable|string|max:255',
            'app_url' => 'required|url',
            'admin_email' => 'required|email',
            'admin_password' => 'required|min:8|confirmed',
            'site_name' => 'required|string|max:255',
        ]);

        $connection = $validated['db_connection'];
        
        // For MySQL, create a buffered connection to avoid "unbuffered queries" errors
        if ($connection === 'mysql') {
            try {
                // Map validated fields to expected config keys
                $dbConfig = [
                    'host' => $validated['db_host'] ?? '127.0.0.1',
                    'port' => $validated['db_port'] ?? 3306,
                    'database' => $validated['db_database'],
                    'username' => $validated['db_username'],
                    'password' => $validated['db_password'] ?? '',
                ];
                $pdo = $this->createBufferedMysqlConnection($dbConfig);
            } catch (\PDOException $e) {
                $errorMessage = $e->getMessage();
                
                // Provide helpful hint for database creation failure
                if (str_contains($errorMessage, 'Unknown database') || str_contains($errorMessage, 'Unknown MySQL database')) {
                    $errorMessage .= ' If your MySQL user doesn\'t have CREATE DATABASE permission, create the database manually first.';
                }
                
                return back()->withErrors(['db_error' => 'Could not connect to the database: ' . $errorMessage]);
            }
        } else {
            // For other database types, use the standard Laravel DB connection
            config(['database.default' => $connection]);
            foreach (['host' => 'db_host', 'port' => 'db_port', 'database' => 'db_database', 'username' => 'db_username', 'password' => 'db_password'] as $setting => $field) {
                if (isset($validated[$field])) {
                    config(["database.connections.{$connection}.{$setting}" => $validated[$field]]);
                }
            }
            DB::purge($connection);
            DB::reconnect($connection);
        }

        // Test database connection and refuse to modify an existing application.
        try {
            // Create database if it doesn't exist (MySQL only)
            if ($connection === 'mysql' && isset($validated['db_host'])) {
                try {
                    // Connect to MySQL server without specifying database with buffered queries enabled
                    $serverConnection = new \PDO(
                        "mysql:host={$validated['db_host']};port=" . ($validated['db_port'] ?? 3306),
                        $validated['db_username'],
                        $validated['db_password'] ?? ''
                    );
                    $serverConnection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                    $serverConnection->setAttribute(\Pdo\Mysql::ATTR_USE_BUFFERED_QUERY, true);
                    
                    // Create database if it doesn't exist
                    $dbName = $validated['db_database'];
                    $serverConnection->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}`");
                    
                    \Log::info('Database created or already exists: ' . $dbName);
                } catch (\PDOException $e) {
                    // Log the warning but don't fail - user might have created it manually
                    \Log::warning('Could not auto-create database (may need manual creation): ' . $e->getMessage());
                }
            }
            
            // Check for existing tables using direct PDO with buffered queries
            $tablesExist = false;
            if ($connection === 'mysql') {
                try {
                    $stmt = DB::connection()->getPdo()->prepare("
                        SELECT COUNT(*) 
                        FROM information_schema.tables 
                        WHERE table_schema = ? 
                        AND table_name IN ('users', 'migrations')
                    ");
                    $stmt->execute([$validated['db_database']]);
                    $count = $stmt->fetchColumn();
                    $tablesExist = (int)$count > 0;
                } catch (\Exception $e) {
                    \Log::warning('Could not check for existing tables: ' . $e->getMessage());
                }
            } else {
                // For non-MySQL databases, use Schema facade
                $tablesExist = Schema::hasTable('users') || Schema::hasTable('migrations');
            }
            
            if ($tablesExist && !config('app.installed')) {
                return back()->withErrors(['db_error' => 'The selected database already contains application tables. Use an empty database.']);
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            
            // Provide helpful hint for database creation failure
            if (str_contains($errorMessage, 'Unknown database') || str_contains($errorMessage, 'Unknown MySQL database')) {
                $errorMessage .= ' If your MySQL user doesn\'t have CREATE DATABASE permission, create the database manually first.';
            }
            
            return back()->withErrors(['db_error' => 'Could not connect to the database: ' . $errorMessage]);
        }

        // Generate app key if not exists
        if (!config('app.key')) {
            $key = Str::random(32);
            config(['app.key' => "base64:" . base64_encode($key)]);
        }

        // Run migrations
        try {
            if (Artisan::call('migrate', ['--force' => true]) !== 0) {
                throw new \RuntimeException('Migration failed. Check the server log.');
            }
        } catch (\Exception $e) {
            return back()->withErrors(['migrate_error' => 'Failed to run migrations: ' . $e->getMessage()]);
        }

        // Create admin user
        try {
            $user = \App\Models\User::create([
                'name' => 'Admin',
                'email' => $validated['admin_email'],
                'password' => Hash::make($validated['admin_password']),
            ]);

            $role = \App\Models\Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Administrator']);
            $user->roles()->attach($role);
        } catch (\Exception $e) {
            return back()->withErrors(['user_error' => 'Failed to create admin user: ' . $e->getMessage()]);
        }

        // Update site settings
        try {
            \App\Models\Site::create(['name' => $validated['site_name'], 'description' => 'Just another Rosetta CMS site']);
        } catch (\Exception $e) {
            // Non-critical, continue
        }

        // Store configuration
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $this->updateEnvironment([
                'APP_URL' => $validated['app_url'],
                'DB_CONNECTION' => $validated['db_connection'],
                'DB_HOST' => $validated['db_host'] ?? '127.0.0.1',
                'DB_PORT' => $validated['db_port'] ?? '3306',
                'DB_DATABASE' => $validated['db_database'],
                'DB_USERNAME' => $validated['db_username'],
                'DB_PASSWORD' => $validated['db_password'] ?? '',
                'INSTALL_ENABLED' => 'false',
            ]);
        }

        // Clear config cache
        Artisan::call('config:clear');

        return redirect()->route('login')->with('success', 'Installation completed successfully! You can now log in.');
    }

    /**
     * Run installation checks.
     */
    private function runInstallationChecks()
    {
        $checks = [
            'php_version' => $this->checkPHPVersion(),
            'extensions' => $this->checkExtensions(),
            'directories' => $this->checkDirectories(),
            'dependencies' => $this->checkDependencies(),
        ];

        $checks['all_passed'] = collect($checks)->every(function ($check) {
            return $check['passed'];
        });

        return $checks;
    }

    /**
     * Check PHP version.
     */
    private function checkPHPVersion()
    {
        $version = phpversion();
        $passed = version_compare($version, '8.1.0', '>=');
        
        return [
            'name' => 'PHP Version',
            'required' => '8.1.0 or higher',
            'current' => $version,
            'passed' => $passed,
            'message' => $passed ? "PHP {$version} is supported" : "PHP {$version} is too old, need 8.1.0+",
        ];
    }

    /**
     * Check required PHP extensions.
     */
    private function checkExtensions()
    {
        $requiredExtensions = [
            'openssl' => 'OpenSSL',
            'pdo' => 'PDO',
            'mbstring' => 'Multibyte String',
            'tokenizer' => 'Tokenizer',
            'JSON' => 'JSON',
            'xml' => 'XML',
            'ctype' => 'CTYPE',
            'fileinfo' => 'File Information',
            'curl' => 'cURL',
            'gd' => 'GD',
        ];

        $extensions = [];
        foreach ($requiredExtensions as $ext => $name) {
            $loaded = extension_loaded($ext);
            $extensions[] = [
                'name' => $name,
                'passed' => $loaded,
                'message' => $loaded ? "Extension {$name} is loaded" : "Extension {$name} is not loaded",
            ];
        }

        return [
            'name' => 'PHP Extensions',
            'extensions' => $extensions,
            'passed' => collect($extensions)->every(function ($e) { return $e['passed']; }),
            'message' => 'All required PHP extensions are installed',
        ];
    }

    /**
     * Check writable directories.
     */
    private function checkDirectories()
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

        $dirChecks = [];
        foreach ($directories as $name => $path) {
            $writable = is_writable($path);
            $dirChecks[] = [
                'name' => $name,
                'path' => $path,
                'passed' => $writable,
                'message' => $writable ? "Directory {$name} is writable" : "Directory {$name} is not writable",
            ];
        }

        return [
            'name' => 'Directory Permissions',
            'directories' => $dirChecks,
            'passed' => collect($dirChecks)->every(function ($d) { return $d['passed']; }),
            'message' => 'All required directories are writable',
        ];
    }

    /**
     * Check if dependencies are installed.
     */
    private function checkDependencies()
    {
        $hasAutoloader = file_exists(base_path('vendor/autoload.php'));
        $hasComposerLock = file_exists(base_path('composer.lock'));

        return [
            'name' => 'Dependencies',
            'has_autoloader' => $hasAutoloader,
            'has_composer_lock' => $hasComposerLock,
            'passed' => $hasAutoloader,
            'message' => $hasAutoloader ? 'Laravel dependencies are installed' : 'Run "composer install" to install dependencies',
        ];
    }

    /**
     * Update .env file.
     */
    private function updateEnvironment(array $values)
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            return;
        }

        $content = file_get_contents($envPath);
        
        foreach ($values as $key => $value) {
            $escapedValue = str_replace('"', '\"', $value);
            if (preg_match("/^{$key}=.*/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}=\"{$escapedValue}\"", $content);
            } else {
                $content .= "\n{$key}=\"{$escapedValue}\"\n";
            }
        }

        file_put_contents($envPath, $content);
    }

    /**
     * Create a MySQL connection with buffered queries enabled.
     */
    private function createBufferedMysqlConnection(array $config)
    {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
        
        $options = [
            \Pdo\Mysql::ATTR_USE_BUFFERED_QUERY => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        $pdo = new PDO($dsn, $config['username'], $config['password'] ?? '', $options);
        
        // Set the Laravel DB connection to use this PDO instance
        DB::purge('mysql');
        config([
            'database.connections.mysql.host' => $config['host'],
            'database.connections.mysql.port' => $config['port'],
            'database.connections.mysql.database' => $config['database'],
            'database.connections.mysql.username' => $config['username'],
            'database.connections.mysql.password' => $config['password'] ?? '',
            'database.connections.mysql.charset' => 'utf8mb4',
            'database.connections.mysql.collation' => 'utf8mb4_unicode_ci',
            'database.connections.mysql.strict' => true,
        ]);
        
        DB::connection('mysql')->setPdo($pdo);
        DB::setDefaultConnection('mysql');
        
        return $pdo;
    }
}
