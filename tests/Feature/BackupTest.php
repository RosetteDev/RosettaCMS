<?php

namespace Tests\Feature;

use App\Models\Backup;
use App\Models\Post;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $editor;
    protected Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('local');

        $this->admin = User::factory()->create();
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin', 'permissions' => []]);
        $this->admin->roles()->attach($adminRole);

        $this->editor = User::factory()->create();
        $editorRole = Role::create(['name' => 'Editor', 'slug' => 'editor', 'permissions' => []]);
        $this->editor->roles()->attach($editorRole);

        $this->site = Site::create([
            'name' => 'Backup Test Site',
            'slug' => 'backup-test-site',
            'domain' => 'backup.test',
        ]);
    }

    protected function makePost(string $title = 'Original Post'): Post
    {
        return Post::create([
            'site_id' => $this->site->id,
            'user_id' => $this->admin->id,
            'title' => $title,
            'slug' => 'original-post',
            'content' => '<p>Body</p>',
            'post_type' => 'post',
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function test_admin_can_view_backups_page(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/backups')
            ->assertOk()
            ->assertSee('Backups');
    }

    public function test_non_admin_cannot_view_backups_page(): void
    {
        $this->actingAs($this->editor)
            ->get('/admin/backups')
            ->assertForbidden();
    }

    public function test_create_backup_produces_archive_and_record(): void
    {
        Storage::disk('public')->put('media/uploads/hello.jpg', 'fake-image-bytes');

        $backup = app(BackupService::class)->create($this->admin);

        $this->assertInstanceOf(Backup::class, $backup);
        $this->assertDatabaseHas('backups', ['id' => $backup->id]);
        $this->assertGreaterThan(0, $backup->size_bytes);
        $this->assertGreaterThan(0, $backup->tables_count);
        $this->assertSame(1, $backup->media_count);

        Storage::disk('local')->assertExists($backup->path);
    }

    public function test_restore_replaces_database_contents(): void
    {
        $post = $this->makePost();

        $backup = app(BackupService::class)->create($this->admin);

        $post->update(['title' => 'Tampered Title']);

        app(BackupService::class)->restore($backup);

        $this->assertSame('Original Post', $post->fresh()->title);
    }

    public function test_restore_replaces_media_files(): void
    {
        Storage::disk('public')->put('media/uploads/hello.jpg', 'original-bytes');

        $backup = app(BackupService::class)->create($this->admin);

        Storage::disk('public')->put('media/uploads/hello.jpg', 'tampered-bytes');
        Storage::disk('public')->put('media/uploads/extra.jpg', 'extra-bytes');

        app(BackupService::class)->restore($backup);

        $this->assertSame('original-bytes', Storage::disk('public')->get('media/uploads/hello.jpg'));
        $this->assertFalse(Storage::disk('public')->exists('media/uploads/extra.jpg'));
    }

    public function test_admin_can_create_backup_via_web(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/backups')
            ->assertRedirect(route('admin.backups.index'));

        $this->assertDatabaseHas('backups', ['created_by' => $this->admin->id]);
    }

    public function test_non_admin_cannot_create_backup(): void
    {
        $this->actingAs($this->editor)
            ->post('/admin/backups')
            ->assertForbidden();
    }

    public function test_export_json_returns_content(): void
    {
        $this->makePost('Export Me Post');

        $response = $this->actingAs($this->admin)
            ->get('/admin/backups/export/json');

        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'attachment; filename="rosetta-content-export.json"');

        $data = $response->json();
        $this->assertArrayHasKey('posts', $data);
        $titles = array_column($data['posts'], 'title');
        $this->assertContains('Export Me Post', $titles);
    }

    public function test_export_csv_returns_csv_file(): void
    {
        $this->makePost('CSV Export Post');

        $response = $this->actingAs($this->admin)
            ->get('/admin/backups/export/csv');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->assertStringContainsString('CSV Export Post', $response->getContent());
    }

    public function test_admin_can_download_backup(): void
    {
        $backup = app(BackupService::class)->create($this->admin);

        $response = $this->actingAs($this->admin)
            ->get('/admin/backups/' . $backup->id . '/download');

        $response->assertOk();
    }

    public function test_restore_via_web_requires_confirmation(): void
    {
        $backup = app(BackupService::class)->create($this->admin);

        $this->actingAs($this->admin)
            ->post('/admin/backups/' . $backup->id . '/restore', [])
            ->assertSessionHasErrors('confirm');
    }

    public function test_import_json_creates_content(): void
    {
        $payload = [
            'posts' => [
                ['title' => 'Imported Post', 'slug' => 'imported-post', 'content' => '<p>Hi</p>', 'post_type' => 'post', 'status' => 'published', 'published_at' => now()->toDateTimeString()],
            ],
            'pages' => [
                ['title' => 'Imported Page', 'slug' => 'imported-page', 'content' => '<p>Page</p>', 'post_type' => 'page', 'status' => 'draft'],
            ],
            'categories' => [
                ['name' => 'Imported Category', 'slug' => 'imported-category'],
            ],
            'tags' => [
                ['name' => 'Imported Tag', 'slug' => 'imported-tag'],
            ],
        ];

        $summary = app(BackupService::class)->importContent($payload, $this->admin);

        $this->assertSame(1, $summary['posts']);
        $this->assertSame(1, $summary['pages']);
        $this->assertSame(1, $summary['categories']);
        $this->assertSame(1, $summary['tags']);
        $this->assertSame(0, $summary['skipped']);

        $this->assertDatabaseHas('posts', ['slug' => 'imported-post', 'post_type' => 'post']);
        $this->assertDatabaseHas('posts', ['slug' => 'imported-page', 'post_type' => 'page']);
        $this->assertDatabaseHas('categories', ['slug' => 'imported-category']);
        $this->assertDatabaseHas('tags', ['slug' => 'imported-tag']);
    }

    public function test_import_json_skips_existing_posts(): void
    {
        $this->makePost();

        $payload = [
            'posts' => [
                ['title' => 'Dup', 'slug' => 'original-post', 'content' => '<p>Dup</p>', 'status' => 'draft'],
            ],
        ];

        $summary = app(BackupService::class)->importContent($payload, $this->admin);

        $this->assertSame(0, $summary['posts']);
        $this->assertSame(1, $summary['skipped']);
        $this->assertSame(1, Post::where('slug', 'original-post')->count());
    }

    public function test_import_csv_creates_skeleton_posts(): void
    {
        $csv = "type,title,slug,status,published_at,created_at\n" .
            "post,CSV Post,csv-post,published,2026-11-01 10:00:00,2026-11-01 10:00:00\n" .
            "page,CSV Page,csv-page,draft,,2026-11-01 10:00:00\n";

        $summary = app(BackupService::class)->importCsv($csv, $this->admin);

        $this->assertSame(1, $summary['posts']);
        $this->assertSame(1, $summary['pages']);
        $this->assertDatabaseHas('posts', ['slug' => 'csv-post', 'post_type' => 'post', 'status' => 'published']);
        $this->assertDatabaseHas('posts', ['slug' => 'csv-page', 'post_type' => 'page', 'status' => 'draft']);
    }

    public function test_admin_can_import_json_via_web(): void
    {
        $payload = [
            'posts' => [['title' => 'Web Post', 'slug' => 'web-post', 'content' => '<p>Body</p>', 'status' => 'draft']],
        ];

        $file = tempnam(sys_get_temp_dir(), 'import-') . '.json';
        file_put_contents($file, json_encode($payload));

        $response = $this->actingAs($this->admin)
            ->post('/admin/backups/import', [
                'file' => new \Illuminate\Http\UploadedFile($file, 'import.json', 'application/json', null, true),
            ]);

        @unlink($file);

        $response->assertRedirect(route('admin.backups.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('posts', ['slug' => 'web-post']);
    }

    public function test_non_admin_cannot_import(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'import-') . '.json';
        file_put_contents($file, json_encode(['posts' => []]));

        $response = $this->actingAs($this->editor)
            ->post('/admin/backups/import', [
                'file' => new \Illuminate\Http\UploadedFile($file, 'import.json', 'application/json', null, true),
            ]);

        @unlink($file);

        $response->assertForbidden();
    }

    public function test_import_rejects_invalid_extension(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'import-') . '.txt';
        file_put_contents($file, 'not json');

        $response = $this->actingAs($this->admin)
            ->post('/admin/backups/import', [
                'file' => new \Illuminate\Http\UploadedFile($file, 'import.txt', 'text/plain', null, true),
            ]);

        @unlink($file);

        $response->assertRedirect(route('admin.backups.index'));
        $response->assertSessionHas('error');
    }

    public function test_admin_can_delete_backup(): void
    {
        $backup = app(BackupService::class)->create($this->admin);

        $this->actingAs($this->admin)
            ->delete('/admin/backups/' . $backup->id)
            ->assertRedirect(route('admin.backups.index'));

        $this->assertDatabaseMissing('backups', ['id' => $backup->id]);
    }
}
