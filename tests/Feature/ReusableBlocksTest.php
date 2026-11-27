<?php

namespace Tests\Feature;

use App\Models\ReusableBlock;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReusableBlocksTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin', 'permissions' => []]);
        $this->admin->roles()->attach($adminRole);

        $this->actingAs($this->admin);
    }

    public function test_index_lists_reusable_blocks(): void
    {
        ReusableBlock::create([
            'name' => 'CTA Banner',
            'description' => 'Reusable CTA',
            'payload' => [['type' => 'button', 'content' => ['text' => 'Go', 'url' => '#'], 'settings' => []]],
        ]);

        $response = $this->get(route('admin.reusable-blocks.index'));

        $response->assertOk();
        $response->assertSee('CTA Banner');
    }

    public function test_store_creates_reusable_block_from_json(): void
    {
        $response = $this->postJson(route('admin.reusable-blocks.store'), [
            'name' => 'Hero CTA',
            'description' => 'Reusable hero CTA',
            'blocks' => [
                ['type' => 'heading', 'content' => ['level' => 'h2', 'text' => 'Hello'], 'settings' => []],
                ['type' => 'button', 'content' => ['text' => 'Click', 'url' => '#'], 'settings' => []],
            ],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('reusableBlock.name', 'Hero CTA');
        $response->assertJsonCount(2, 'reusableBlock.blocks');

        $this->assertDatabaseHas('reusable_blocks', ['name' => 'Hero CTA']);
    }

    public function test_store_rejects_empty_blocks(): void
    {
        $response = $this->postJson(route('admin.reusable-blocks.store'), [
            'name' => 'Empty',
            'blocks' => [],
        ]);

        $response->assertStatus(422);
    }

    public function test_store_requires_name(): void
    {
        $response = $this->postJson(route('admin.reusable-blocks.store'), [
            'blocks' => [['type' => 'text', 'content' => ['text' => 'Hi']]],
        ]);

        $response->assertStatus(422);
    }

    public function test_destroy_deletes_reusable_block(): void
    {
        $reusable = ReusableBlock::create([
            'name' => 'Delete Me',
            'payload' => [['type' => 'text', 'content' => ['text' => 'x'], 'settings' => []]],
        ]);

        $response = $this->deleteJson(route('admin.reusable-blocks.destroy', $reusable));

        $response->assertOk();
        $this->assertDatabaseMissing('reusable_blocks', ['id' => $reusable->id]);
    }

    public function test_reusable_block_model_returns_blocks_array(): void
    {
        $reusable = ReusableBlock::create([
            'name' => 'Model Test',
            'payload' => [['type' => 'quote', 'content' => ['text' => 'q'], 'settings' => []]],
        ]);

        $this->assertSame(1, $reusable->blockCount());
        $this->assertSame('quote', $reusable->blocks()[0]['type']);
    }

    public function test_page_editor_renders_reusable_block_ui(): void
    {
        $site = \App\Models\Site::create([
            'name' => 'Editor Test Site',
            'slug' => 'editor-test-site',
            'domain' => 'editor-test.test',
        ]);
        $page = \App\Models\Post::create([
            'site_id' => $site->id,
            'user_id' => $this->admin->id,
            'title' => 'Editor Test Page',
            'slug' => 'editor-test-page',
            'content' => '<p>Body</p>',
            'post_type' => 'page',
            'status' => 'published',
            'published_at' => now(),
        ]);
        \App\Models\Block::create([
            'post_id' => $page->id,
            'site_id' => $site->id,
            'type' => 'heading',
            'content' => ['level' => 'h2', 'text' => 'Existing Heading'],
            'order' => 1,
        ]);
        ReusableBlock::create([
            'name' => 'CTA Banner',
            'payload' => [['type' => 'button', 'content' => ['text' => 'Go', 'url' => '#'], 'settings' => []]],
        ]);

        $response = $this->get(route('admin.pages.edit', $page->id));

        $response->assertOk();
        $response->assertSee('Save as Reusable');
        $response->assertSee('saveReusableModal');
        $response->assertSee('Reusable blocks');
        $response->assertSee('CTA Banner');
    }

    public function test_block_patterns_are_valid(): void
    {
        $patterns = \App\Support\BlockPatterns::all();
        $validTypes = array_keys(\App\Models\Block::getTypeOptions());

        $this->assertNotEmpty($patterns);

        foreach ($patterns as $pattern) {
            $this->assertNotEmpty($pattern['id']);
            $this->assertNotEmpty($pattern['name']);
            $this->assertNotEmpty($pattern['blocks']);

            foreach ($pattern['blocks'] as $block) {
                $this->assertContains($block['type'], $validTypes);
                $this->assertIsArray($block['content']);
                $this->assertIsArray($block['settings']);
            }
        }
    }

    public function test_pattern_lookup_by_id(): void
    {
        $this->assertNotNull(\App\Support\BlockPatterns::find('hero-cta'));
        $this->assertNull(\App\Support\BlockPatterns::find('does-not-exist'));
    }

    public function test_page_editor_lists_patterns_in_add_modal(): void
    {
        $site = \App\Models\Site::create([
            'name' => 'Pattern Test Site',
            'slug' => 'pattern-test-site',
            'domain' => 'pattern-test.test',
        ]);
        $page = \App\Models\Post::create([
            'site_id' => $site->id,
            'user_id' => $this->admin->id,
            'title' => 'Pattern Test Page',
            'slug' => 'pattern-test-page',
            'post_type' => 'page',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('admin.pages.edit', $page->id));

        $response->assertOk();
        $response->assertSee('Patterns');
        $response->assertSee('Hero + CTA');
        $response->assertSee('Quote Strip');
    }

    public function test_index_open_page_builder_links_to_latest_page_edit(): void
    {
        $site = \App\Models\Site::create([
            'name' => 'Builder Target Site',
            'slug' => 'builder-target-site',
            'domain' => 'builder-target.test',
        ]);
        $page = \App\Models\Post::create([
            'site_id' => $site->id,
            'user_id' => $this->admin->id,
            'title' => 'Builder Target Page',
            'slug' => 'builder-target-page',
            'post_type' => 'page',
            'status' => 'draft',
        ]);

        $response = $this->get(route('admin.reusable-blocks.index'));

        $response->assertOk();
        $response->assertSee(route('admin.pages.edit', $page->id), false);
    }

    public function test_index_open_page_builder_falls_back_to_create_without_pages(): void
    {
        $response = $this->get(route('admin.reusable-blocks.index'));

        $response->assertOk();
        $response->assertSee(route('admin.pages.create'), false);
    }

    public function test_index_requires_authentication(): void
    {
        auth()->logout();

        $response = $this->get(route('admin.reusable-blocks.index'));

        $response->assertRedirect();
    }
}
