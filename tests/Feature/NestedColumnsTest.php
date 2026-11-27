<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Post;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NestedColumnsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Site $site;
    protected Post $page;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();

        $this->site = Site::create([
            'name' => 'Columns Test Site',
            'slug' => 'columns-test-site',
            'domain' => 'columns-test.test',
        ]);

        $this->page = Post::create([
            'site_id' => $this->site->id,
            'user_id' => $this->admin->id,
            'title' => 'Columns Test Page',
            'slug' => 'columns-test-page',
            'content' => '<p>Body</p>',
            'post_type' => 'page',
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    private function createBlock(array $attributes): Block
    {
        return Block::create(array_merge([
            'post_id' => $this->page->id,
            'site_id' => $this->site->id,
            'order' => 1,
        ], $attributes));
    }

    public function test_columns_block_renders_children_inside_columns(): void
    {
        $columns = $this->createBlock([
            'type' => 'columns',
            'content' => ['columns' => 2],
            'order' => 1,
        ]);

        $childA = $this->createBlock([
            'type' => 'text',
            'content' => ['text' => 'Left column text'],
            'parent_block_id' => $columns->id,
            'order' => 1,
        ]);

        $childB = $this->createBlock([
            'type' => 'text',
            'content' => ['text' => 'Right column text'],
            'parent_block_id' => $columns->id,
            'order' => 2,
        ]);

        $html = $columns->fresh()->render();

        $this->assertStringContainsString('Left column text', $html);
        $this->assertStringContainsString('Right column text', $html);
        $this->assertSame(2, substr_count($html, 'class="col"'));
    }

    public function test_children_are_not_rendered_as_top_level_blocks_in_the_view(): void
    {
        $columns = $this->createBlock([
            'type' => 'columns',
            'content' => ['columns' => 2],
            'order' => 1,
        ]);

        $this->createBlock([
            'type' => 'text',
            'content' => ['text' => 'Child one'],
            'parent_block_id' => $columns->id,
            'order' => 1,
        ]);

        $this->createBlock([
            'type' => 'text',
            'content' => ['text' => 'Child two'],
            'parent_block_id' => $columns->id,
            'order' => 2,
        ]);

        $this->page->load('blocks');

        $html = view('themes.rosetta.partials.blocks', ['post' => $this->page])->render();

        // Children appear once each, inside the columns — never as standalone blocks.
        $this->assertSame(1, substr_count($html, 'Child one'));
        $this->assertSame(1, substr_count($html, 'Child two'));
    }

    public function test_empty_columns_block_renders_empty_columns(): void
    {
        $columns = $this->createBlock([
            'type' => 'columns',
            'content' => ['columns' => 3],
            'order' => 1,
        ]);

        $html = $columns->fresh()->render();

        $this->assertSame(3, substr_count($html, 'class="col"'));
        $this->assertStringNotContainsString('Unknown block type', $html);
    }

    public function test_nested_columns_render_recursively(): void
    {
        $outer = $this->createBlock([
            'type' => 'columns',
            'content' => ['columns' => 2],
            'order' => 1,
        ]);

        $inner = $this->createBlock([
            'type' => 'columns',
            'content' => ['columns' => 2],
            'parent_block_id' => $outer->id,
            'order' => 1,
        ]);

        $this->createBlock([
            'type' => 'text',
            'content' => ['text' => 'Deep text'],
            'parent_block_id' => $inner->id,
            'order' => 1,
        ]);

        $html = $outer->fresh()->render();

        $this->assertStringContainsString('Deep text', $html);
        $this->assertSame(2, substr_count($html, 'row g-3'));
    }

    public function test_depth_guard_stops_pathological_recursion(): void
    {
        $block = $this->createBlock([
            'type' => 'columns',
            'content' => ['columns' => 1],
            'order' => 1,
        ]);

        // Make the block its own child (pathological data).
        $block->update(['parent_block_id' => $block->id]);

        $html = $block->fresh()->render();

        // Must terminate and return a string rather than loop forever.
        $this->assertIsString($html);
    }

    public function test_unknown_block_type_falls_back_to_safe_output(): void
    {
        $block = $this->createBlock([
            'type' => 'does-not-exist',
            'content' => ['text' => 'fallback text'],
            'order' => 1,
        ]);

        $html = $block->fresh()->render();

        $this->assertStringContainsString('Unknown block type', $html);
    }
}
