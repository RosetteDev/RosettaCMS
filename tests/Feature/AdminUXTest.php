<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUXTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Site::create([
            'name' => 'Admin UX Test Site',
            'slug' => 'admin-ux-test-site',
            'domain' => 'admin-ux.test',
        ]);

        $this->user = User::factory()->create([
            'email' => 'admin-ux@test.dev',
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

    public function test_notifications_page_renders_for_staff(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/notifications');

        $response->assertStatus(200);
        $response->assertSee('Notifications');
    }

    public function test_search_endpoint_finds_posts_and_pages(): void
    {
        Post::create([
            'site_id' => 1,
            'user_id' => $this->user->id,
            'post_type' => 'post',
            'title' => 'Zebra Post Alpha',
            'slug' => 'zebra-post-alpha',
            'content' => '<p>x</p>',
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->user)->getJson('/admin/search?q=Zebra');

        $response->assertOk();
        $labels = collect($response->json('results'))->pluck('label');
        $this->assertContains('Zebra Post Alpha', $labels->all());
    }

    public function test_search_requires_authentication(): void
    {
        $this->getJson('/admin/search?q=zebra')->assertStatus(401);
    }

    public function test_theme_preference_persists_to_user(): void
    {
        $response = $this->actingAs($this->user)->postJson('/admin/preferences/theme', [
            'theme' => 'dark',
        ]);

        $response->assertOk();
        $this->assertSame('dark', $this->user->fresh()->admin_theme);
    }

    public function test_theme_preference_rejects_invalid_values(): void
    {
        $response = $this->actingAs($this->user)->postJson('/admin/preferences/theme', [
            'theme' => 'purple',
        ]);

        $response->assertStatus(422);
    }

    public function test_dashboard_layout_persists_order(): void
    {
        $response = $this->actingAs($this->user)->postJson('/admin/dashboard/layout', [
            'order' => ['recent-media', 'quick-actions', 'recent-content'],
        ]);

        $response->assertOk();
        $this->assertSame(
            ['recent-media', 'quick-actions', 'recent-content'],
            $this->user->fresh()->dashboard_layout
        );
    }

    public function test_dashboard_layout_rejects_duplicate_sections(): void
    {
        $response = $this->actingAs($this->user)->postJson('/admin/dashboard/layout', [
            'order' => ['recent-media', 'recent-media', 'quick-actions'],
        ]);

        $response->assertStatus(422);
    }

    public function test_dashboard_renders_sections_in_saved_order(): void
    {
        $this->user->update(['dashboard_layout' => ['recent-media', 'quick-actions', 'recent-content']]);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $content = $response->getContent();

        // Sections carry the saved order as the CSS grid order property.
        $this->assertMatchesRegularExpression(
            '/data-section="recent-media" style="order: 0"/',
            $content
        );
        $this->assertMatchesRegularExpression(
            '/data-section="quick-actions" style="order: 1"/',
            $content
        );
        $this->assertMatchesRegularExpression(
            '/data-section="recent-content" style="order: 2"/',
            $content
        );
    }

    public function test_dashboard_shows_pending_comments_badge(): void
    {
        $post = Post::create([
            'site_id' => 1,
            'user_id' => $this->user->id,
            'post_type' => 'post',
            'title' => 'Comment Test Post',
            'slug' => 'comment-test-post',
            'content' => '<p>x</p>',
            'status' => 'published',
        ]);

        Comment::create([
            'post_id' => $post->id,
            'content' => 'A pending comment',
            'author_name' => 'Tester',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('notification-badge', false);
    }
}
