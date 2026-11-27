<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiMediaMenusTest extends TestCase
{
    use RefreshDatabase;

    protected Site $site;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = Site::create([
            'name' => 'API Test Site',
            'slug' => 'api-test-site',
            'description' => 'Testing media and menu endpoints',
        ]);

        $this->user = User::factory()->create(['name' => 'API Author']);
    }

    protected function makeMedia(string $fileName, string $folder, string $mimeType = 'image/jpeg', ?int $siteId = null): Media
    {
        return Media::create([
            'site_id' => $siteId ?? $this->site->id,
            'user_id' => $this->user->id,
            'file_name' => $fileName,
            'file_path' => 'media/' . $fileName,
            'folder' => $folder,
            'mime_type' => $mimeType,
            'file_size' => 1024,
            'alt_text' => 'Alt for ' . $fileName,
            'caption' => 'Caption for ' . $fileName,
        ]);
    }

    protected function makeMenu(string $name, string $location = 'header'): Menu
    {
        return Menu::create([
            'site_id' => $this->site->id,
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
            'location' => $location,
        ]);
    }

    // ---------- Media listing ----------

    public function test_media_listing_returns_paginated_media(): void
    {
        $this->makeMedia('hero.jpg', 'images');
        $this->makeMedia('about.png', 'images');

        $response = $this->getJson('/api/v1/media?per_page=10')
            ->assertOk()
            ->assertJsonPath('meta.total', 2);

        $names = collect($response->json('data'))->pluck('file_name')->sort()->values()->all();
        $this->assertSame(['about.png', 'hero.jpg'], $names);
    }

    public function test_media_listing_filters_by_folder_and_type(): void
    {
        $this->makeMedia('photo.jpg', 'images', 'image/jpeg');
        $this->makeMedia('clip.mp4', 'videos', 'video/mp4');

        $response = $this->getJson('/api/v1/media?folder=images')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.file_name', 'photo.jpg');

        $response = $this->getJson('/api/v1/media?type=video')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.file_name', 'clip.mp4');
    }

    public function test_media_listing_searches_file_name_alt_and_caption(): void
    {
        $this->makeMedia('hero-banner.jpg', 'images');
        $this->makeMedia('other.png', 'images');

        $response = $this->getJson('/api/v1/media?search=banner')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.file_name', 'hero-banner.jpg');
    }

    public function test_media_listing_does_not_leak_other_sites_media(): void
    {
        $otherSite = Site::create([
            'name' => 'Other Site',
            'slug' => 'other-site',
        ]);

        $this->makeMedia('mine.jpg', 'images');
        $this->makeMedia('not-mine.jpg', 'images', 'image/jpeg', $otherSite->id);

        $response = $this->getJson('/api/v1/media')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.file_name', 'mine.jpg');
    }

    // ---------- Single media ----------

    public function test_single_media_endpoint_returns_the_item(): void
    {
        $media = $this->makeMedia('hero.jpg', 'images');

        $this->getJson('/api/v1/media/' . $media->id)
            ->assertOk()
            ->assertJsonPath('data.id', $media->id)
            ->assertJsonPath('data.file_name', 'hero.jpg')
            ->assertJsonPath('data.is_image', true)
            ->assertJsonPath('data.human_size', '1 KB');
    }

    public function test_single_media_endpoint_404s_for_unknown_id(): void
    {
        $this->getJson('/api/v1/media/9999')->assertNotFound();
    }

    public function test_media_endpoints_are_mirrored_in_the_legacy_namespace(): void
    {
        $media = $this->makeMedia('legacy.jpg', 'images');

        $this->getJson('/api/media')->assertOk()->assertJsonPath('meta.total', 1);
        $this->getJson('/api/media/' . $media->id)->assertOk()->assertJsonPath('data.file_name', 'legacy.jpg');
    }

    // ---------- Menus ----------

    public function test_menus_listing_returns_menus_with_item_counts(): void
    {
        $menu = $this->makeMenu('Main Menu');
        MenuItem::create(['menu_id' => $menu->id, 'title' => 'Home', 'type' => 'custom', 'url' => '/', 'order' => 1]);
        MenuItem::create(['menu_id' => $menu->id, 'title' => 'About', 'type' => 'custom', 'url' => '/about', 'order' => 2]);

        $response = $this->getJson('/api/v1/menus')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'main-menu')
            ->assertJsonPath('data.0.item_count', 2);

        $this->assertCount(1, $response->json('data'));
    }

    public function test_menu_show_returns_the_full_item_tree(): void
    {
        $menu = $this->makeMenu('Main Menu');
        $parent = MenuItem::create(['menu_id' => $menu->id, 'title' => 'Parent', 'type' => 'custom', 'url' => '/parent', 'order' => 1]);
        MenuItem::create(['menu_id' => $menu->id, 'parent_id' => $parent->id, 'title' => 'Child', 'type' => 'custom', 'url' => '/child', 'order' => 1]);
        MenuItem::create(['menu_id' => $menu->id, 'title' => 'Top', 'type' => 'custom', 'url' => '/top', 'order' => 2]);

        $response = $this->getJson('/api/v1/menus/main-menu')
            ->assertOk()
            ->assertJsonPath('data.slug', 'main-menu')
            ->assertJsonPath('data.item_count', 3)
            ->assertJsonPath('data.items.0.title', 'Parent')
            ->assertJsonPath('data.items.0.children.0.title', 'Child')
            ->assertJsonPath('data.items.1.title', 'Top');

        $this->assertCount(2, $response->json('data.items'));
        $this->assertCount(1, $response->json('data.items.0.children'));
    }

    public function test_menu_show_404s_for_unknown_slug(): void
    {
        $this->getJson('/api/v1/menus/does-not-exist')->assertNotFound();
    }

    public function test_menus_do_not_leak_other_sites_menus(): void
    {
        $otherSite = Site::create(['name' => 'Other Site', 'slug' => 'other-site']);
        Menu::create([
            'site_id' => $otherSite->id,
            'name' => 'Other Menu',
            'slug' => 'other-menu',
            'location' => 'header',
        ]);

        $this->makeMenu('Mine');

        $response = $this->getJson('/api/v1/menus')
            ->assertOk();

        $this->assertCount(1, $response->json('data'));
        $this->assertSame('mine', $response->json('data.0.slug'));
    }

    public function test_menus_endpoints_are_mirrored_in_the_legacy_namespace(): void
    {
        $menu = $this->makeMenu('Legacy Menu');

        $this->getJson('/api/menus')->assertOk()->assertJsonPath('data.0.slug', 'legacy-menu');
        $this->getJson('/api/menus/legacy-menu')->assertOk()->assertJsonPath('data.name', 'Legacy Menu');
    }
}
