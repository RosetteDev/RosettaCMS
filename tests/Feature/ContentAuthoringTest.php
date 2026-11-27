<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentAuthoringTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Site::create([
            'name' => 'Authoring Test Site',
            'slug' => 'authoring-test-site',
            'domain' => 'authoring.test',
        ]);

        $this->user = User::factory()->create([
            'email' => 'authoring-admin@test.dev',
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

    protected function createPost(array $overrides = []): Post
    {
        return Post::create(array_merge([
            'site_id' => 1,
            'user_id' => $this->user->id,
            'post_type' => 'post',
            'title' => 'Authoring Test Post',
            'slug' => 'authoring-test-post',
            'content' => '<p>Hello</p>',
            'status' => 'draft',
        ], $overrides));
    }

    public function test_custom_fields_are_saved_to_meta(): void
    {
        $post = $this->createPost();

        $response = $this->actingAs($this->user)->post('/admin/posts/' . $post->id, [
            '_method' => 'PUT',
            'title' => 'Updated Title',
            'slug' => 'authoring-test-post',
            'content' => '<p>Updated</p>',
            'status' => 'draft',
            'custom_fields' => [
                ['key' => 'sku', 'value' => 'ABC-123'],
                ['key' => 'rating', 'value' => '5'],
            ],
        ]);

        $response->assertRedirect(route('admin.posts.edit', $post->id));

        $post->refresh();
        $this->assertSame(['sku' => 'ABC-123', 'rating' => '5'], $post->meta['custom_fields'] ?? null);
    }

    public function test_custom_fields_reject_invalid_keys(): void
    {
        $post = $this->createPost();

        $response = $this->actingAs($this->user)->post('/admin/posts/' . $post->id, [
            '_method' => 'PUT',
            'title' => 'Updated Title',
            'slug' => 'authoring-test-post',
            'content' => '<p>Updated</p>',
            'status' => 'draft',
            'custom_fields' => [
                ['key' => 'Bad Key!', 'value' => 'value'],
            ],
        ]);

        $response->assertSessionHasErrors('custom_fields.0.key');
    }

    public function test_bulk_assign_sets_the_author(): void
    {
        $other = User::factory()->create(['name' => 'Other Author']);
        $post = $this->createPost();

        $response = $this->actingAs($this->user)->postJson('/admin/posts/bulk', [
            'ids' => [$post->id],
            'action' => 'assign',
            'assign_user_id' => $other->id,
        ]);

        $response->assertOk();
        $this->assertSame($other->id, $post->fresh()->user_id);
    }

    public function test_bulk_assign_requires_an_author(): void
    {
        $post = $this->createPost();

        $response = $this->actingAs($this->user)->postJson('/admin/posts/bulk', [
            'ids' => [$post->id],
            'action' => 'assign',
        ]);

        $response->assertStatus(422);
        $this->assertSame($this->user->id, $post->fresh()->user_id);
    }
}
