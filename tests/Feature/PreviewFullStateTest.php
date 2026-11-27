<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Post;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreviewFullStateTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Site $site;
    protected Post $page;
    protected Block $block;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $adminRole = \App\Models\Role::create(['name' => 'Admin', 'slug' => 'admin', 'permissions' => []]);
        $this->admin->roles()->attach($adminRole);

        $this->site = Site::create([
            'name' => 'Preview Site',
            'slug' => 'preview-site',
            'domain' => 'preview.test',
        ]);

        $this->page = Post::create([
            'site_id' => $this->site->id,
            'user_id' => $this->admin->id,
            'title' => 'Preview Page',
            'slug' => 'preview-page',
            'content' => '<p>Body</p>',
            'post_type' => 'page',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->block = Block::create([
            'post_id' => $this->page->id,
            'site_id' => $this->site->id,
            'type' => 'text',
            'content' => ['text' => 'Original text'],
            'order' => 1,
        ]);
    }

    public function test_full_state_payload_renders_unsaved_blocks_and_omits_deleted(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/preview/' . $this->page->id, [
                'blocks' => [
                    ['id' => $this->block->id, 'type' => 'text', 'content' => ['text' => 'Updated text'], 'order' => 1],
                    ['type' => 'heading', 'content' => ['text' => 'Brand new heading', 'level' => 'h2'], 'order' => 2],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $html = $response->json('html');

        $this->assertStringContainsString('Updated text', $html);
        $this->assertStringNotContainsString('Original text', $html);
        $this->assertStringContainsString('Brand new heading', $html);
    }

    public function test_full_state_payload_omits_blocks_absent_from_payload(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/preview/' . $this->page->id, [
                'blocks' => [
                    ['type' => 'text', 'content' => ['text' => 'Only remaining block'], 'order' => 1],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $html = $response->json('html');

        $this->assertStringContainsString('Only remaining block', $html);
        $this->assertStringNotContainsString('Original text', $html);
    }

    public function test_unsaved_block_is_not_persisted_by_preview(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/preview/' . $this->page->id, [
                'blocks' => [
                    ['type' => 'text', 'content' => ['text' => 'Should not persist'], 'order' => 2],
                ],
            ])
            ->assertOk();

        $this->assertSame(1, Block::where('post_id', $this->page->id)->count());
    }

    public function test_full_state_payload_does_not_mutate_persisted_blocks(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/preview/' . $this->page->id, [
                'blocks' => [
                    ['id' => $this->block->id, 'type' => 'text', 'content' => ['text' => 'Unsaved draft'], 'order' => 1],
                ],
            ])
            ->assertOk();

        $this->assertSame('Original text', $this->block->fresh()->content['text']);
    }

    public function test_legacy_change_payload_still_merges_and_renders_all_blocks(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/preview/' . $this->page->id, [
                'blocks' => [['id' => $this->block->id, 'content' => ['text' => 'Merged legacy text']]],
            ])
            ->assertOk();

        $html = $response->json('html');

        $this->assertStringContainsString('Merged legacy text', $html);
    }
}
