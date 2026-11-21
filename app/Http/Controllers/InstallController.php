<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        $validated = $request->validate([
            'db_connection' => 'required|in:mysql,pgsql,sqlite,sqlsrv',
            'db_host' => 'required_if:db_connection,mysql|nullable',
            'db_port' => 'nullable|numeric',
            'db_database' => 'required',
            'db_username' => 'required',
            'db_password' => 'nullable',
            'app_url' => 'required|url',
            'admin_email' => 'required|email',
            'admin_password' => 'required|min:8|confirmed',
            'site_name' => 'required',
        ]);

        // Test database connection
        try {
            $testConnection = DB::connection()->getPDO();
            $testConnection->exec('SELECT 1');
        } catch (\Exception $e) {
            return back()->withErrors(['db_error' => 'Could not connect to the database: ' . $e->getMessage()]);
        }

        // Generate app key if not exists
        if (!config('app.key')) {
            $key = Str::random(32);
            config(['app.key' => "base64:" . base64_encode($key)]);
        }

        // Run migrations
        try {
            Artisan::call('migrate:fresh', ['--force' => true]);
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

            $role = \App\Models\Role::firstOrCreate(['name' => 'admin', 'display_name' => 'Administrator']);
            $user->roles()->attach($role);
        } catch (\Exception $e) {
            return back()->withErrors(['user_error' => 'Failed to create admin user: ' . $e->getMessage()]);
        }

        // Update site settings
        try {
            $site = \App\Models\Site::first();
            if ($site) {
                $site->update([
                    'name' => $validated['site_name'],
                    'description' => 'Just another Rosetta CMS site',
                ]);
            }
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
}
