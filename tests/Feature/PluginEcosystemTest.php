<?php

namespace Tests\Feature;

use App\Models\PluginApproval;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use App\Services\PluginEcosystemService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PluginEcosystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $pluginDir;

    protected function setUp(): void
    {
        parent::setUp();

        Site::create([
            'name' => 'Ecosystem Test Site',
            'slug' => 'ecosystem-test-site',
            'domain' => 'ecosystem.test',
        ]);

        $this->user = User::factory()->create([
            'email' => 'ecosystem-admin@test.dev',
            'password' => bcrypt('correct-horse'),
        ]);

        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Test admin',
            'permissions' => [],
        ]);
        $this->user->roles()->attach($adminRole);

        $this->pluginDir = base_path('plugins/ecosystem-test-plugin');
        File::ensureDirectoryExists($this->pluginDir . '/src');
        File::put($this->pluginDir . '/plugin.json', json_encode([
            'name' => 'Ecosystem Test Plugin',
            'slug' => 'ecosystem-test-plugin',
            'version' => '1.0.0',
            'description' => 'Test plugin',
            'author' => 'Tester',
            'entry' => 'src/index.php',
            'permissions' => ['filesystem', 'network'],
        ]));
        File::put($this->pluginDir . '/src/index.php', "<?php\n\$ok = 'fine';\necho \$ok;\n");
    }

    protected function tearDown(): void
    {
        if (is_dir($this->pluginDir)) {
            File::deleteDirectory($this->pluginDir);
        }

        parent::tearDown();
    }

    public function test_discover_finds_manifest_with_permissions(): void
    {
        $service = new PluginEcosystemService();
        $plugins = array_filter($service->discover(), fn ($p) => $p['slug'] === 'ecosystem-test-plugin');

        $this->assertCount(1, $plugins);
        $plugin = array_values($plugins)[0];
        $this->assertSame(['filesystem', 'network'], $plugin['permissions']);
        $this->assertSame([], $plugin['errors']);
        $this->assertSame([], $plugin['findings']);
    }

    public function test_discover_flags_invalid_manifest(): void
    {
        File::put($this->pluginDir . '/plugin.json', '{"name": "no-version"}');

        $service = new PluginEcosystemService();
        $plugins = array_filter($service->discover(), fn ($p) => $p['slug'] === 'ecosystem-test-plugin');

        $this->assertCount(1, $plugins);
        $plugin = array_values($plugins)[0];
        $this->assertNotEmpty($plugin['errors']);
    }

    public function test_static_scan_flags_dangerous_code(): void
    {
        File::put($this->pluginDir . '/src/index.php', "<?php\n\$ok = 'fine';\nsystem('ls -la');\n");

        $service = new PluginEcosystemService();
        $plugins = array_filter($service->discover(), fn ($p) => $p['slug'] === 'ecosystem-test-plugin');
        $plugin = array_values($plugins)[0];

        $this->assertNotEmpty($plugin['findings']);
        $this->assertSame('critical', $plugin['findings'][0]['severity']);
    }

    public function test_approve_permission_flow(): void
    {
        $response = $this->actingAs($this->user)->post('/admin/plugins/ecosystem-test-plugin/permissions/filesystem/approve');

        $response->assertRedirect();
        $this->assertDatabaseHas('plugin_approvals', [
            'plugin_id' => 'ecosystem-test-plugin',
            'permission' => 'filesystem',
        ]);

        $response = $this->actingAs($this->user)->post('/admin/plugins/ecosystem-test-plugin/permissions/filesystem/revoke');
        $response->assertRedirect();
        $this->assertDatabaseMissing('plugin_approvals', [
            'plugin_id' => 'ecosystem-test-plugin',
            'permission' => 'filesystem',
        ]);
    }

    public function test_plugins_index_renders_ecosystem_section(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/plugins');

        $response->assertStatus(200);
        $response->assertSee('ecosystem-test-plugin');
        $response->assertSee('Third-Party Ecosystem');
    }
}
