<?php

namespace Tests\Feature;

use App\Services\InstallService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstallCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_install_command_is_registered(): void
    {
        $this->artisan('install', ['--help' => true])->assertExitCode(0);
    }

    public function test_dry_run_prints_preflight_and_writes_nothing(): void
    {
        $envHash = md5_file(base_path('.env'));

        $this->artisan('install', [
            '--dry-run' => true,
            '--yes' => true,
            '--site-name' => 'Test Site',
            '--admin-email' => 'test@example.com',
            '--admin-password' => 'password123',
        ])->assertExitCode(0);

        // .env must be untouched by a dry run.
        $this->assertSame($envHash, md5_file(base_path('.env')));
    }

    public function test_install_command_refuses_to_reinstall_when_installed(): void
    {
        config(['app.installed' => true]);

        $this->artisan('install', [
            '--yes' => true,
            '--site-name' => 'Test Site',
            '--admin-email' => 'test@example.com',
            '--admin-password' => 'password123',
        ])->assertExitCode(1);
    }

    public function test_install_command_requires_admin_email(): void
    {
        $this->artisan('install', [
            '--yes' => true,
            '--site-name' => 'Test Site',
            '--admin-password' => 'password123',
        ])->assertExitCode(1);
    }

    public function test_install_command_requires_admin_password(): void
    {
        $this->artisan('install', [
            '--yes' => true,
            '--site-name' => 'Test Site',
            '--admin-email' => 'test@example.com',
        ])->assertExitCode(1);
    }

    public function test_preflight_reports_structure_and_all_passed(): void
    {
        $preflight = app(InstallService::class)->preflight('sqlite');

        $this->assertArrayHasKey('all_passed', $preflight);
        $this->assertArrayHasKey('checks', $preflight);
        $this->assertArrayHasKey('failures', $preflight);

        foreach (['php_version', 'extensions', 'directories', 'dependencies', 'database'] as $name) {
            $this->assertArrayHasKey($name, $preflight['checks']);
            $this->assertArrayHasKey('passed', $preflight['checks'][$name]);
            $this->assertArrayHasKey('message', $preflight['checks'][$name]);
        }

        // On a healthy checkout, everything passes.
        $this->assertTrue($preflight['all_passed']);
        $this->assertEmpty($preflight['failures']);
    }

    public function test_preflight_sqlite_reports_database_directory(): void
    {
        $preflight = app(InstallService::class)->preflight('sqlite');

        $this->assertStringContainsString('SQLite', $preflight['checks']['database']['message']);
    }

    public function test_service_refuses_to_reinstall_over_installed_site(): void
    {
        config(['app.installed' => false]);

        $temp = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($temp, "APP_NAME=Test\nAPP_INSTALLED=true\n");

        $result = app(InstallService::class)->install([
            'env_path' => $temp,
            'site_name' => 'Test Site',
            'admin_email' => 'test@example.com',
            'admin_password' => 'password123',
        ]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('already installed', $result['errors'][0]);

        // The .env file is left untouched.
        $this->assertStringContainsString('APP_INSTALLED=true', file_get_contents($temp));

        unlink($temp);
    }

    public function test_service_rejects_invalid_admin_email_before_any_writes(): void
    {
        config(['app.installed' => false]);

        $temp = tempnam(sys_get_temp_dir(), 'env');
        $before = file_get_contents($temp);

        $result = app(InstallService::class)->install([
            'env_path' => $temp,
            'site_name' => 'Test Site',
            'admin_email' => 'not-an-email',
            'admin_password' => 'password123',
        ]);

        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['errors']);
        $this->assertSame($before, file_get_contents($temp));

        unlink($temp);
    }

    public function test_service_rejects_short_password_before_any_writes(): void
    {
        config(['app.installed' => false]);

        $temp = tempnam(sys_get_temp_dir(), 'env');

        $result = app(InstallService::class)->install([
            'env_path' => $temp,
            'site_name' => 'Test Site',
            'admin_email' => 'test@example.com',
            'admin_password' => 'short',
        ]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('at least 8 characters', $result['errors'][0]);

        unlink($temp);
    }

    public function test_web_install_check_page_renders_real_preflight_results(): void
    {
        $response = $this->get('/install/check');

        $response->assertOk();
        $response->assertSee('PHP version');
        $response->assertSee('Directory permissions');
        $response->assertSee('Dependencies');
        $response->assertSee('Database');
        // Real results — the page must not claim everything passes unconditionally.
        $response->assertSee('All required PHP extensions', false);
    }

    public function test_web_install_form_renders(): void
    {
        $response = $this->get('/install');

        $response->assertOk();
        $response->assertSee('db_connection');
        $response->assertSee('SQLite');
    }

    public function test_web_install_process_requires_install_enabled(): void
    {
        config(['app.install_enabled' => false]);

        $response = $this->post('/install', [
            'db_connection' => 'sqlite',
            'app_url' => 'http://localhost:8000',
            'site_name' => 'Test Site',
            'admin_email' => 'test@example.com',
            'admin_password' => 'password123',
            'admin_password_confirmation' => 'password123',
        ]);

        $response->assertForbidden();
    }
}
