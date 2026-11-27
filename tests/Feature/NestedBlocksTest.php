<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Post;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NestedBlocksTest extends TestCase
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
            'name' => 'Nested Site',
            'slug' => 'nested-site',
            'domain' => 'nested.test',
        ]);

        $this->page = Post::create([
            'site_id' => $this->site->id,
            'user_id' => $this->admin->id,
            'title' => 'Nested Page',
            'slug' => 'nested-page',
            'content' => '<p>Legacy content</p>',
            'post_type' => 'page',
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function test_store_creates_child_block_with_parent(): void
    {
        $parent = Block::create([
            'post_id' => $this->page->id,
            'site_id' => $this->site->id,
            'type' => 'columns',
            'content' => ['columns' => 2, 'gutter' => 20],
            'order' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson('/admin/pages/' . $this->page->id . '/blocks', [
                'type' => 'text',
                'parent_id' => $parent->id,
                'content' => ['text' => 'Nested child text'],
                'order' => 1,
            ]);

        $response->assertOk();
        $this->assertSame('Nested child text', $response->json('block.content.text'));
        $this->assertSame($parent->id, $response->json('block.parent_block_id'));
    }

    public function test_preview_renders_nested_children(): void
    {
        $parent = Block::create([
            'post_id' => $this->page->id,
            'site_id' => $this->site->id,
            'type' => 'columns',
            'content' => ['columns' => 2, 'gutter' => 20],
            'order' => 1,
        ]);

        $this->parentChild = Block::create([
            'post_id' => $this->page->id,
            'site_id' => $this->site->id,
            'parent_block_id' => $parent->id,
            'type' => 'text',
            'content' => ['text' => 'Persisted nested text'],
            'order' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson('/preview/' . $this->page->id, [
                'blocks' => [
                    [
                        'id' => $parent->id,
                        'type' => 'columns',
                        'content' => ['columns' => 2, 'gutter' => 20],
                        'settings' => [],
                        'order' => 1,
                        'children' => [
                            ['id' => $this->parentChild->id, 'type' => 'text', 'content' => ['text' => 'Persisted nested text'], 'order' => 1],
                            ['type' => 'heading', 'content' => ['level' => 'h2', 'text' => 'Unsaved Nested Heading'], 'order' => 2],
                        ],
                    ],
                ],
            ]);

        $response->assertOk();
        $response->assertSee('Unsaved Nested Heading');
        $response->assertSee('Persisted nested text');
    }

    public function test_frontend_preview_renders_unsaved_nested_children_from_session(): void
    {
        $parent = Block::create([
            'post_id' => $this->page->id,
            'site_id' => $this->site->id,
            'type' => 'columns',
            'content' => ['columns' => 2, 'gutter' => 20],
            'order' => 1,
        ]);

        // The editor pushes its live state, including nested children.
        $this->actingAs($this->admin)
            ->postJson('/preview/' . $this->page->id, [
                'blocks' => [
                    [
                        'id' => $parent->id,
                        'type' => 'columns',
                        'content' => ['columns' => 2, 'gutter' => 20],
                        'settings' => [],
                        'order' => 1,
                        'children' => [
                            ['type' => 'text', 'content' => ['text' => 'Live nested body copy'], 'order' => 1],
                        ],
                    ],
                ],
            ])
            ->assertOk();

        $response = $this->actingAs($this->admin)
            ->get('/preview/' . $this->page->id);

        $response->assertOk();
        $response->assertSee('Live nested body copy');
    }

    public function test_page_editor_renders_nested_children_in_config(): void
    {
        $parent = Block::create([
            'post_id' => $this->page->id,
            'site_id' => $this->site->id,
            'type' => 'columns',
            'content' => ['columns' => 2, 'gutter' => 20],
            'order' => 1,
        ]);

        Block::create([
            'post_id' => $this->page->id,
            'site_id' => $this->site->id,
            'parent_block_id' => $parent->id,
            'type' => 'text',
            'content' => ['text' => 'Editor sees this'],
            'order' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/pages/' . $this->page->id . '/edit');

        $response->assertOk();
        $response->assertSee('Editor sees this');
        $response->assertSee('Nested blocks');
    }
}
