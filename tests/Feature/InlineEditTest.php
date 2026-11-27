<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Post;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InlineEditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $author;
    protected Site $site;
    protected Post $page;
    protected Block $block;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin', 'permissions' => []]);
        $this->admin->roles()->attach($adminRole);

        $this->author = User::factory()->create();
        $authorRole = Role::create(['name' => 'Author', 'slug' => 'author', 'permissions' => []]);
        $this->author->roles()->attach($authorRole);

        $this->site = Site::create([
            'name' => 'Inline Edit Site',
            'slug' => 'inline-edit-site',
            'domain' => 'inline-edit.test',
        ]);

        $this->page = Post::create([
            'site_id' => $this->site->id,
            'user_id' => $this->admin->id,
            'title' => 'Inline Edit Page',
            'slug' => 'inline-edit-page',
            'content' => '<p>Body</p>',
            'post_type' => 'page',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->block = Block::create([
            'post_id' => $this->page->id,
            'site_id' => $this->site->id,
            'type' => 'text',
            'content' => ['text' => 'Hello inline edit'],
            'order' => 1,
        ]);
    }

    public function test_inline_edit_show_renders_the_page_with_the_modal(): void
    {
        $this->actingAs($this->admin)
            ->get('/inline-edit/' . $this->page->id)
            ->assertOk()
            ->assertSee('Inline Edit')
            ->assertSee('Hello inline edit');
    }

    public function test_update_block_persists_content(): void
    {
        $this->actingAs($this->admin)
            ->putJson('/inline-edit/' . $this->page->id . '/blocks/' . $this->block->id, [
                'content' => ['text' => 'Updated text'],
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame('Updated text', $this->block->fresh()->content['text']);
    }

    public function test_update_block_merges_content_instead_of_replacing_it(): void
    {
        Block::create([
            'post_id' => $this->page->id,
            'site_id' => $this->site->id,
            'type' => 'heading',
            'content' => ['text' => 'Title', 'level' => 'h2'],
            'order' => 2,
        ]);

        $this->actingAs($this->admin)
            ->putJson('/inline-edit/' . $this->page->id . '/blocks/' . $this->block->id, [
                'content' => ['text' => 'New text'],
            ])
            ->assertOk();

        $fresh = $this->block->fresh()->content;
        $this->assertSame('New text', $fresh['text']);
    }

    public function test_update_block_rejects_users_without_edit_permission(): void
    {
        $user = User::factory()->create(); // no roles, no permissions

        $this->actingAs($user)
            ->putJson('/inline-edit/' . $this->page->id . '/blocks/' . $this->block->id, [
                'content' => ['text' => 'Nope'],
            ])
            ->assertForbidden();

        $this->assertSame('Hello inline edit', $this->block->fresh()->content['text']);
    }

    public function test_update_block_returns_404_for_a_block_from_another_post(): void
    {
        $otherPage = Post::create([
            'site_id' => $this->site->id,
            'user_id' => $this->admin->id,
            'title' => 'Other Page',
            'slug' => 'other-page',
            'post_type' => 'page',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $foreignBlock = Block::create([
            'post_id' => $otherPage->id,
            'site_id' => $this->site->id,
            'type' => 'text',
            'content' => ['text' => 'Foreign'],
            'order' => 1,
        ]);

        $this->actingAs($this->admin)
            ->putJson('/inline-edit/' . $this->page->id . '/blocks/' . $foreignBlock->id, [
                'content' => ['text' => 'Hijack'],
            ])
            ->assertNotFound();
    }

    public function test_destroy_block_deletes_the_block_and_its_children(): void
    {
        $child = Block::create([
            'post_id' => $this->page->id,
            'site_id' => $this->site->id,
            'parent_block_id' => $this->block->id,
            'type' => 'text',
            'content' => ['text' => 'Child'],
            'order' => 2,
        ]);

        $this->actingAs($this->admin)
            ->deleteJson('/inline-edit/' . $this->page->id . '/blocks/' . $this->block->id)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertNull(Block::find($this->block->id));
        $this->assertNull(Block::find($child->id));
    }

    public function test_inline_edit_modal_renders_wired_controls(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/inline-edit/' . $this->page->id);

        $response->assertOk();
        // Controls are wired to handlers, not dead buttons.
        $response->assertSee('moveBlock(');
        $response->assertSee('deleteBlock(');
        // Save flow is honest: disabled until a change is made.
        $response->assertSee('inlineEditSaveBtn');
        $response->assertSee('inlineEditStatus');
    }

    public function test_preview_returns_rendered_blocks_html(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/preview/' . $this->page->id, [
                'blocks' => [['id' => $this->block->id, 'content' => ['text' => 'Preview text']]],
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertStringContainsString('Preview text', $response->json('html'));
    }

    public function test_preview_rejects_users_without_edit_permission(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/preview/' . $this->page->id, [
                'blocks' => [['id' => $this->block->id, 'content' => ['text' => 'Nope']]],
            ])
            ->assertForbidden();
    }
}
