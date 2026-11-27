<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Post;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendLivePreviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Site $site;
    protected Post $page;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin', 'permissions' => []]);
        $this->admin->roles()->attach($adminRole);

        $this->site = Site::create([
            'name' => 'Live Preview Site',
            'slug' => 'live-preview-site',
            'domain' => 'live-preview.test',
        ]);

        $this->page = Post::create([
            'site_id' => $this->site->id,
            'user_id' => $this->admin->id,
            'title' => 'Live Preview Page',
            'slug' => 'live-preview-page',
            'content' => '<p>Legacy content</p>',
            'post_type' => 'page',
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function test_frontend_page_renders_blocks_instead_of_legacy_content(): void
    {
        Block::create([
            'post_id' => $this->page->id,
            'site_id' => $this->site->id,
            'type' => 'heading',
            'content' => ['level' => 'h1', 'text' => 'Rendered From The Builder'],
            'order' => 1,
        ]);

        $response = $this->get('/pages/' . $this->page->slug);

        $response->assertOk();
        $response->assertSee('Rendered From The Builder');
        // The legacy content section is replaced by the blocks partial.
        $response->assertDontSee('page-content post-content', false);
    }

    public function test_frontend_page_keeps_legacy_content_when_no_blocks(): void
    {
        $response = $this->get('/pages/' . $this->page->slug);

        $response->assertOk();
        $response->assertSee('Legacy content');
    }

    public function test_frontend_preview_replays_session_live_state(): void
    {
        Block::create([
            'post_id' => $this->page->id,
            'site_id' => $this->site->id,
            'type' => 'text',
            'content' => ['text' => 'Persisted text'],
            'order' => 1,
        ]);

        // Simulate the editor pushing its live state to the JSON preview endpoint.
        $this->actingAs($this->admin)
            ->postJson('/preview/' . $this->page->id, [
                'blocks' => [
                    ['type' => 'heading', 'content' => ['level' => 'h2', 'text' => 'Unsaved Live Heading'], 'order' => 1],
                ],
            ])
            ->assertOk();

        // The frontend preview route should replay the stored live state.
        $response = $this->actingAs($this->admin)
            ->get('/preview/' . $this->page->id);

        $response->assertOk();
        $response->assertSee('Unsaved Live Heading');
        $response->assertDontSee('Persisted text');
    }

    public function test_preview_json_stores_full_state_in_session(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/preview/' . $this->page->id, [
                'blocks' => [
                    ['type' => 'text', 'content' => ['text' => 'Session stored text'], 'order' => 1],
                ],
            ])
            ->assertOk();

        $stored = session()->get('preview.changes.' . $this->page->id);

        $this->assertIsArray($stored);
        $this->assertSame('text', $stored[0]['type']);
        $this->assertSame('Session stored text', $stored[0]['content']['text']);
    }
}
