<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Site::create([
            'name' => 'Upload Test Site',
            'slug' => 'upload-test-site',
            'domain' => 'upload.test',
        ]);

        $this->user = User::factory()->create([
            'email' => 'upload-admin@test.dev',
            'password' => bcrypt('correct-horse'),
        ]);

        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Test admin',
            'permissions' => [],
        ]);
        $this->user->roles()->attach($adminRole);
    }

    public function test_upload_rejects_a_zip_without_theme_json(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'bad-theme-');
        $zip = new \ZipArchive();
        $zip->open($tmp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('random.txt', 'not a theme');
        $zip->close();

        $response = $this->actingAs($this->user)->post('/admin/themes/upload', [
            'theme_zip' => new \Illuminate\Http\UploadedFile($tmp, 'bad.zip', 'application/zip', null, true),
        ]);

        @unlink($tmp);

        $response->assertRedirect(route('admin.themes.index'));
        $response->assertSessionHas('error');
    }

    public function test_upload_rejects_path_traversal(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'evil-theme-');
        $zip = new \ZipArchive();
        $zip->open($tmp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('../evil.txt', 'pwned');
        $zip->close();

        $response = $this->actingAs($this->user)->post('/admin/themes/upload', [
            'theme_zip' => new \Illuminate\Http\UploadedFile($tmp, 'evil.zip', 'application/zip', null, true),
        ]);

        @unlink($tmp);

        $response->assertRedirect(route('admin.themes.index'));
        $response->assertSessionHas('error');
    }

    public function test_upload_installs_a_valid_theme_zip(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'good-theme-');
        $zip = new \ZipArchive();
        $zip->open($tmp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('good-theme/theme.json', json_encode([
            'slug' => 'good-theme',
            'name' => 'Good Theme',
            'version' => '1.0.0',
            'description' => 'Valid test theme',
            'author' => 'Test',
        ]));
        $zip->addFromString('good-theme/css/style.css', 'body { margin: 0; }');
        $zip->close();

        $response = $this->actingAs($this->user)->post('/admin/themes/upload', [
            'theme_zip' => new \Illuminate\Http\UploadedFile($tmp, 'good.zip', 'application/zip', null, true),
        ]);

        @unlink($tmp);

        $response->assertRedirect(route('admin.themes.index'));
        $response->assertSessionHas('success');
        $this->assertFileExists(base_path('themes/good-theme/theme.json'));

        // Clean up the installed theme.
        \Illuminate\Support\Facades\File::deleteDirectory(base_path('themes/good-theme'));
    }

    public function test_upload_rejects_invalid_slug(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'badslug-theme-');
        $zip = new \ZipArchive();
        $zip->open($tmp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('badslug-theme/theme.json', json_encode([
            'slug' => 'Bad Slug!',
            'name' => 'Bad Slug Theme',
            'version' => '1.0.0',
        ]));
        $zip->close();

        $response = $this->actingAs($this->user)->post('/admin/themes/upload', [
            'theme_zip' => new \Illuminate\Http\UploadedFile($tmp, 'badslug.zip', 'application/zip', null, true),
        ]);

        @unlink($tmp);

        $response->assertRedirect(route('admin.themes.index'));
        $response->assertSessionHas('error');
    }
}
