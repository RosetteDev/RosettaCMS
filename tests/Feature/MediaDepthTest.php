<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MediaDepthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Site::create(['name' => 'Test Site', 'slug' => 'test', 'domain' => 'test.test']);

        $this->user = User::factory()->create();
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin', 'description' => '', 'permissions' => []]);
        $this->user->roles()->attach($adminRole);
        $this->actingAs($this->user);
    }

    protected function createMedia(array $attrs = []): Media
    {
        return Media::create(array_merge([
            'user_id' => $this->user->id,
            'site_id' => Site::first()->id,
            'file_name' => '1780000000_abc123_product-photo.jpg',
            'file_path' => 'uploads/1780000000_abc123_product-photo.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 1000,
            'alt_text' => 'existing alt',
        ], $attrs));
    }

    public function test_bulk_rename_renames_file_and_record(): void
    {
        $media = $this->createMedia();

        $this->postJson(route('admin.media.bulk'), [
            'ids' => [$media->id],
            'action' => 'rename',
            'name_base' => 'launch-photo',
        ])->assertOk()->assertJson(['success' => true]);

        $media->refresh();
        $this->assertSame('launch-photo.jpg', $media->file_name);
        $this->assertStringEndsWith('launch-photo.jpg', $media->file_path);
    }

    public function test_bulk_rename_requires_name_base(): void
    {
        $media = $this->createMedia();

        $this->postJson(route('admin.media.bulk'), [
            'ids' => [$media->id],
            'action' => 'rename',
        ])->assertStatus(422)->assertJson(['success' => false]);
    }

    public function test_bulk_set_alt_text_applies_to_images_only(): void
    {
        $image = $this->createMedia(['file_name' => '1780000000_abc123_a.jpg', 'file_path' => 'uploads/a.jpg']);
        $video = $this->createMedia([
            'file_name' => '1780000000_abc123_v.mp4',
            'file_path' => 'uploads/v.mp4',
            'mime_type' => 'video/mp4',
            'alt_text' => null,
        ]);

        $this->postJson(route('admin.media.bulk'), [
            'ids' => [$image->id, $video->id],
            'action' => 'set_alt_text',
            'alt_text' => 'A brand new alt',
        ])->assertOk()->assertJson(['success' => true]);

        $image->refresh();
        $video->refresh();
        $this->assertSame('A brand new alt', $image->alt_text);
        $this->assertNull($video->alt_text);
    }

    public function test_bulk_set_alt_text_requires_text(): void
    {
        $image = $this->createMedia();

        $this->postJson(route('admin.media.bulk'), [
            'ids' => [$image->id],
            'action' => 'set_alt_text',
            'alt_text' => '',
        ])->assertStatus(422);
    }

    public function test_generate_alt_derives_from_filename(): void
    {
        $media = $this->createMedia();

        $response = $this->postJson(route('admin.media.generateAlt', $media));

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertSame('Product Photo', $response->json('alt_text'));
    }

    public function test_generate_alt_rejects_non_images(): void
    {
        $media = $this->createMedia([
            'file_name' => '1780000000_abc123_clip.mp4',
            'file_path' => 'uploads/clip.mp4',
            'mime_type' => 'video/mp4',
        ]);

        $this->postJson(route('admin.media.generateAlt', $media))->assertStatus(422);
    }

    public function test_replace_requires_file(): void
    {
        $media = $this->createMedia();

        $this->postJson(route('admin.media.replace', $media), [])->assertStatus(422);
    }

    public function test_replace_updates_metadata_and_file(): void
    {
        $media = $this->createMedia();
        $path = storage_path('app/public/' . $media->file_path);
        @mkdir(dirname($path), 0755, true);
        file_put_contents($path, 'old-bytes');

        $file = UploadedFile::fake()->create('new-photo.png', 10, 'image/png');

        $this->postJson(route('admin.media.replace', $media), [
            'file' => $file,
        ])->assertOk()->assertJson(['success' => true]);

        $media->refresh();
        $this->assertSame('image/png', $media->mime_type);
        $this->assertSame($file->getSize(), $media->file_size);
        $this->assertFileExists($path);

        @unlink($path);
    }

    public function test_replace_rejects_disallowed_types(): void
    {
        $media = $this->createMedia();

        $file = UploadedFile::fake()->create('evil.exe', 5, 'application/x-msdownload');

        $this->postJson(route('admin.media.replace', $media), [
            'file' => $file,
        ])->assertStatus(422);
    }
}
