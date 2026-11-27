<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Site::create([
            'name' => 'Enforcement Test Site',
            'slug' => 'enforcement-test-site',
            'domain' => 'enforcement.test',
        ]);

        $this->user = User::factory()->create([
            'email' => 'enforcer@test.dev',
            'password' => bcrypt('correct-horse'),
        ]);
    }

    protected function giveRole(array $overrides): Role
    {
        $role = Role::create(array_merge([
            'name' => 'Test Role',
            'slug' => 'editor',
            'description' => 'Enforcement test role',
            'permissions' => [],
        ], $overrides));

        $this->user->roles()->attach($role);

        return $role;
    }

    protected function createPost(array $overrides = []): Post
    {
        return Post::create(array_merge([
            'site_id' => 1,
            'user_id' => $this->user->id,
            'post_type' => 'post',
            'title' => 'Enforcement Test Post',
            'slug' => 'enforcement-test-post',
            'content' => '<p>Original</p>',
            'status' => 'draft',
        ], $overrides));
    }

    public function test_role_without_permissions_cannot_view_posts_index(): void
    {
        $this->giveRole(['permissions' => []]);

        $response = $this->actingAs($this->user)->get('/admin/posts');

        $response->assertForbidden();
    }

    public function test_role_with_flat_posts_permission_can_view_posts_index(): void
    {
        $this->giveRole(['permissions' => ['posts' => true]]);

        $response = $this->actingAs($this->user)->get('/admin/posts');

        $response->assertOk();
    }

    public function test_role_with_view_only_cannot_create_post(): void
    {
        $this->giveRole([
            'permissions' => [],
            'post_type_permissions' => [
                'post' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false, 'publish' => false],
            ],
        ]);

        $response = $this->actingAs($this->user)->get('/admin/posts/create');

        $response->assertForbidden();
    }

    public function test_role_with_edit_only_cannot_delete_post(): void
    {
        $this->giveRole([
            'permissions' => [],
            'post_type_permissions' => [
                'post' => ['view' => true, 'create' => false, 'edit' => true, 'delete' => false, 'publish' => false],
            ],
        ]);

        $post = $this->createPost();

        $response = $this->actingAs($this->user)->delete('/admin/posts/' . $post->id);

        $response->assertForbidden();
        $this->assertNotNull(Post::find($post->id));
    }

    public function test_role_with_edit_only_can_update_post(): void
    {
        $this->giveRole([
            'permissions' => [],
            'post_type_permissions' => [
                'post' => ['view' => true, 'create' => false, 'edit' => true, 'delete' => false, 'publish' => false],
            ],
        ]);

        $post = $this->createPost();

        $response = $this->actingAs($this->user)->put('/admin/posts/' . $post->id, [
            'title' => 'Updated Enforcement Post',
            'slug' => 'enforcement-test-post',
            'content' => '<p>Updated</p>',
            'status' => 'draft',
        ]);

        $response->assertRedirect();

        $post->refresh();
        $this->assertSame('Updated Enforcement Post', $post->title);
    }

    public function test_role_cannot_publish_when_publish_permission_is_denied(): void
    {
        $this->giveRole([
            'permissions' => [],
            'post_type_permissions' => [
                'post' => ['view' => true, 'create' => false, 'edit' => true, 'delete' => false, 'publish' => false],
            ],
        ]);

        $post = $this->createPost(['status' => 'draft']);

        $this->actingAs($this->user)->put('/admin/posts/' . $post->id, [
            'title' => $post->title,
            'slug' => $post->slug,
            'content' => $post->content,
            'status' => 'published',
        ]);

        $post->refresh();
        $this->assertSame('draft', $post->status);
    }

    public function test_role_cannot_edit_fields_not_granted(): void
    {
        $this->giveRole([
            'permissions' => [],
            'post_type_permissions' => [
                'post' => ['view' => true, 'create' => false, 'edit' => true, 'delete' => false, 'publish' => true],
            ],
            'field_permissions' => [
                'post' => ['title'],
            ],
        ]);

        $post = $this->createPost(['content' => '<p>Original content</p>']);

        $this->actingAs($this->user)->put('/admin/posts/' . $post->id, [
            'title' => 'New Title',
            'slug' => $post->slug,
            'content' => '<p>Tampered content</p>',
            'status' => 'draft',
        ]);

        $post->refresh();
        $this->assertSame('New Title', $post->title);
        $this->assertSame('<p>Original content</p>', $post->content);
    }
}
