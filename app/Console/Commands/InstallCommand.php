<?php

namespace App\Console\Commands;

use App\Services\InstallService;
use Illuminate\Console\Command;

/**
 * One-command installer: `php artisan install`
 *
 * Preflight checks → prompts (or --yes) → .env → migrations → seed → lock off.
 * Idempotent: refuses to re-install over an existing configured site unless
 * --force is passed.
 */
class InstallCommand extends Command
{
    protected $signature = 'install
                            {--site-name= : Site name}
                            {--app-url= : Application URL}
                            {--admin-email= : Admin email}
                            {--admin-password= : Admin password (min 8 characters)}
                            {--db-connection=sqlite : Database connection (sqlite, mysql, pgsql, sqlsrv)}
                            {--db-host= : Database host}
                            {--db-port= : Database port}
                            {--db-database= : Database name}
                            {--db-username= : Database username}
                            {--db-password= : Database password}
                            {--force : Reinstall over an existing configured site}
                            {--dry-run : Print the preflight report and planned steps without making changes}
                            {--yes : Non-interactive mode; use options/defaults and do not prompt}';

    protected $description = 'Install Rosetta CMS: preflight checks, .env, migrations and seed';

    public function handle(InstallService $installer): int
    {
        $this->newLine();
        $this->line('<fg=#7c3aed>Rosetta CMS Installer</>');
        $this->line('<fg=gray>Runs preflight checks, writes .env, migrates and seeds the database.</>');
        $this->newLine();

        $options = $this->resolveOptions($installer);

        if ($options === null) {
            return 1;
        }

        // Preflight
        $preflight = $installer->preflight($options['db_connection'], [
            'host' => $options['db_host'],
            'port' => $options['db_port'],
            'database' => $options['db_database'],
            'username' => $options['db_username'],
            'password' => $options['db_password'],
        ]);

        $this->line('Preflight checks');
        foreach ($preflight['checks'] as $check) {
            $mark = $check['passed'] ? '<fg=green>✓</>' : '<fg=red>✗</>';
            $this->line("  {$mark} {$check['name']}: {$check['message']}");
        }
        $this->newLine();

        if (!$preflight['all_passed']) {
            $this->error('Preflight failed — fix the issues above and re-run.');
            foreach ($preflight['failures'] as $failure) {
                $this->line("  • {$failure}");
            }
            return 1;
        }

        if ($options['dry_run']) {
            $this->line('Dry run — no changes will be made. Planned steps:');
            $this->line('  1. Write .env (site name, URL, app key, database, lock off install)');
            $this->line('  2. Run migrations');
            $this->line('  3. Create the site, settings, admin role and admin user');
            $this->line('  4. Lock the installer off (INSTALL_ENABLED=false, APP_INSTALLED=true)');
            if (!$options['force'] && $installer->isInstalled()) {
                $this->newLine();
                $this->warn('Note: this environment is already installed — a real run would refuse without --force.');
            }
            return 0;
        }

        // Idempotency guard (before any writes).
        if (!$options['force'] && $installer->isInstalled()) {
            $this->error('Rosetta CMS is already installed.');
            $this->line('Pass --force to reinstall over the existing site.');
            return 1;
        }

        $this->line('Installing…');
        $result = $installer->install([
            'site_name' => $options['site_name'],
            'app_url' => $options['app_url'],
            'admin_email' => $options['admin_email'],
            'admin_password' => $options['admin_password'],
            'db_connection' => $options['db_connection'],
            'db_host' => $options['db_host'],
            'db_port' => $options['db_port'],
            'db_database' => $options['db_database'],
            'db_username' => $options['db_username'],
            'db_password' => $options['db_password'],
            'force' => $options['force'],
        ]);

        foreach ($result['warnings'] as $warning) {
            $this->warn($warning);
        }

        if (!$result['success']) {
            foreach ($result['errors'] as $error) {
                $this->error($error);
            }
            return 1;
        }

        $this->newLine();
        $this->line('<fg=green>✓ Rosetta CMS installed successfully</>');
        $this->line("  Site:  {$options['site_name']}");
        $this->line("  URL:   {$options['app_url']}");
        $this->line("  Admin: {$options['admin_email']}");
        $this->newLine();
        $this->line('Log in at ' . rtrim($options['app_url'], '/') . '/login');

        return 0;
    }

    /**
     * Resolve options, prompting interactively when needed.
     * Returns null (after printing an error) if required input is missing.
     */
    private function resolveOptions(InstallService $installer): ?array
    {
        $interactive = $this->input->isInteractive() && !$this->option('yes');

        $siteName = $this->option('site-name');
        $appUrl = $this->option('app-url');
        $adminEmail = $this->option('admin-email');
        $adminPassword = $this->option('admin-password');
        $dbConnection = $this->option('db-connection') ?: 'sqlite';
        $dbHost = $this->option('db-host');
        $dbPort = $this->option('db-port');
        $dbDatabase = $this->option('db-database');
        $dbUsername = $this->option('db-username');
        $dbPassword = $this->option('db-password');

        if ($interactive) {
            $siteName = $siteName ?: $this->ask('Site name', 'Rosetta CMS');
            $appUrl = $appUrl ?: $this->ask('Application URL', 'http://localhost:8000');
            $adminEmail = $adminEmail ?: $this->ask('Admin email');

            if (!$adminPassword) {
                $adminPassword = $this->secret('Admin password (min 8 characters)');
                $confirm = $this->secret('Confirm admin password');
                if ($adminPassword !== $confirm) {
                    $this->error('Passwords do not match.');
                    return null;
                }
            }

            if ($dbConnection !== 'sqlite') {
                $this->warn("SQLite is the default — you chose {$dbConnection}.");
                $dbHost = $dbHost ?: $this->ask('Database host', '127.0.0.1');
                $dbPort = $dbPort ?: $this->ask('Database port', '3306');
                $dbDatabase = $dbDatabase ?: $this->ask('Database name');
                $dbUsername = $dbUsername ?: $this->ask('Database username');
                if ($dbPassword === null) {
                    $dbPassword = $this->secret('Database password');
                }
            }
        }

        $errors = [];

        if (!$siteName) {
            $errors[] = 'A site name is required (--site-name).';
        }
        if (!$adminEmail || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid admin email is required (--admin-email).';
        }
        if (strlen((string) $adminPassword) < 8) {
            $errors[] = 'An admin password of at least 8 characters is required (--admin-password).';
        }
        if ($dbConnection !== 'sqlite' && !$dbDatabase) {
            $errors[] = 'A database name is required for ' . $dbConnection . ' (--db-database).';
        }
        if ($dbConnection !== 'sqlite' && !$dbUsername) {
            $errors[] = 'A database username is required for ' . $dbConnection . ' (--db-username).';
        }

        if ($errors) {
            foreach ($errors as $error) {
                $this->error($error);
            }
            if (!$interactive) {
                $this->line('Re-run with the missing options, or omit --yes to be prompted.');
            }
            return null;
        }

        return [
            'site_name' => trim((string) $siteName),
            'app_url' => rtrim(trim((string) ($appUrl ?: 'http://localhost:8000')), '/'),
            'admin_email' => trim((string) $adminEmail),
            'admin_password' => (string) $adminPassword,
            'db_connection' => $dbConnection,
            'db_host' => $dbHost ?: '127.0.0.1',
            'db_port' => $dbPort ?: ($dbConnection === 'sqlite' ? null : '3306'),
            'db_database' => $dbDatabase,
            'db_username' => $dbUsername,
            'db_password' => $dbPassword,
            'force' => (bool) $this->option('force'),
            'dry_run' => (bool) $this->option('dry-run'),
        ];
    }
}
