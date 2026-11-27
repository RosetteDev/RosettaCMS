<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemePreviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin', 'permissions' => []]);
        $this->admin->roles()->attach($adminRole);

        $this->site = Site::create([
            'name' => 'Theme Preview Site',
            'slug' => 'theme-preview-site',
        ]);

        Post::create([
            'site_id' => $this->site->id,
            'user_id' => $this->admin->id,
            'title' => 'Elegant Post',
            'slug' => 'elegant-post',
            'content' => '<p>Post body content</p>',
            'excerpt' => 'A short excerpt',
            'post_type' => 'post',
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function test_admin_can_preview_elegant_theme(): void
    {
        $this->actingAs($this->admin)
            ->get('/?theme=elegant')
            ->assertOk()
            ->assertSee('Georgia')
            ->assertSee('Elegant Post');
    }

    public function test_admin_can_preview_modern_theme(): void
    {
        $this->actingAs($this->admin)
            ->get('/?theme=modern')
            ->assertOk()
            ->assertSee('Inter')
            ->assertSee('Elegant Post');
    }

    public function test_non_admin_preview_param_is_ignored(): void
    {
        $this->get('/?theme=elegant')
            ->assertOk()
            ->assertDontSee('Georgia');
    }

    public function test_admin_can_preview_elegant_single_post(): void
    {
        $this->actingAs($this->admin)
            ->get('/posts/elegant-post?theme=elegant')
            ->assertOk()
            ->assertSee('Post body content');
    }

    public function test_admin_can_preview_modern_single_post(): void
    {
        $this->actingAs($this->admin)
            ->get('/posts/elegant-post?theme=modern')
            ->assertOk()
            ->assertSee('Post body content');
    }

    public function test_admin_can_preview_elegant_search(): void
    {
        $this->actingAs($this->admin)
            ->get('/search?q=Elegant&theme=elegant')
            ->assertOk()
            ->assertSee('Elegant Post');
    }

    public function test_admin_can_preview_elegant_about_page(): void
    {
        $this->actingAs($this->admin)
            ->get('/about?theme=elegant')
            ->assertOk()
            ->assertSee('About');
    }

    public function test_admin_can_preview_elegant_category_and_tag(): void
    {
        $category = \App\Models\Category::create([
            'site_id' => $this->site->id,
            'name' => 'News',
            'slug' => 'news',
        ]);
        $post = Post::where('slug', 'elegant-post')->first();
        $post->categories()->attach($category);

        $this->actingAs($this->admin)
            ->get('/categories/news?theme=elegant')
            ->assertOk()
            ->assertSee('News');
    }

    public function test_preview_rejects_invalid_slug(): void
    {
        $this->actingAs($this->admin)
            ->get('/?theme=does-not-exist')
            ->assertOk()
            ->assertDontSee('Georgia');
    }
}
