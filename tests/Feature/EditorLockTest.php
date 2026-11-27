<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostLock;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditorLockTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $editor;
    protected User $other;
    protected Post $post;
    protected Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = Site::create([
            'name' => 'Lock Test Site',
            'slug' => 'lock-test-site',
            'domain' => 'lock.test',
        ]);

        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Admin',
            'permissions' => ['dashboard' => true, 'posts' => true, 'pages' => true],
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Lock Admin',
            'email' => 'lock-admin@test.dev',
            'password' => bcrypt('correct-horse'),
        ]);
        $this->admin->roles()->attach($adminRole);

        // A dedicated editor role with the flat posts/pages permission so the
        // lock endpoints are reachable for non-admin editors.
        $editorRole = Role::create([
            'name' => 'Editor',
            'slug' => 'editor',
            'description' => 'Editor',
            'permissions' => ['dashboard' => true, 'posts' => true, 'pages' => true],
        ]);

        $this->editor = User::factory()->create([
            'name' => 'Editor One',
            'email' => 'lock-editor@test.dev',
            'password' => bcrypt('correct-horse'),
        ]);
        $this->editor->roles()->attach($editorRole);

        $this->other = User::factory()->create([
            'name' => 'Other Person',
            'email' => 'lock-other@test.dev',
            'password' => bcrypt('correct-horse'),
        ]);
        $this->other->roles()->attach($editorRole);

        $this->post = Post::create([
            'site_id' => $this->site->id,
            'user_id' => $this->admin->id,
            'title' => 'Lockable Post',
            'slug' => 'lockable-post',
            'content' => '<p>body</p>',
            'post_type' => 'post',
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    protected function makeLock(User $user, string $heartbeat = 'now'): PostLock
    {
        return PostLock::create([
            'post_id' => $this->post->id,
            'user_id' => $user->id,
            'locked_at' => now(),
            'heartbeat_at' => $heartbeat === 'stale' ? now()->subMinutes(5) : now(),
        ]);
    }

    public function test_acquire_creates_lock_for_current_user(): void
    {
        $response = $this->actingAs($this->editor)->postJson('/admin/posts/' . $this->post->id . '/lock');

        $response->assertOk();
        $response->assertJson(['me' => true, 'conflict' => false]);
        $response->assertJsonPath('holder.name', 'Editor One');

        $this->assertDatabaseHas('post_locks', [
            'post_id' => $this->post->id,
            'user_id' => $this->editor->id,
        ]);
    }

    public function test_acquire_does_not_steal_lock_from_another_user(): void
    {
        $this->makeLock($this->other);

        $response = $this->actingAs($this->editor)->postJson('/admin/posts/' . $this->post->id . '/lock');

        $response->assertOk();
        $response->assertJson(['me' => false, 'conflict' => true]);
        $response->assertJsonPath('holder.name', 'Other Person');

        // The other user's lock must remain untouched.
        $this->assertDatabaseHas('post_locks', [
            'post_id' => $this->post->id,
            'user_id' => $this->other->id,
        ]);
        $this->assertDatabaseMissing('post_locks', [
            'post_id' => $this->post->id,
            'user_id' => $this->editor->id,
        ]);
    }

    public function test_stale_lock_is_treated_as_expired(): void
    {
        $this->makeLock($this->other, 'stale');

        $response = $this->actingAs($this->editor)->postJson('/admin/posts/' . $this->post->id . '/lock');

        $response->assertOk();
        $response->assertJson(['me' => true, 'conflict' => false]);

        $this->assertDatabaseMissing('post_locks', [
            'post_id' => $this->post->id,
            'user_id' => $this->other->id,
        ]);
    }

    public function test_release_removes_current_users_lock(): void
    {
        $this->makeLock($this->editor);

        $response = $this->actingAs($this->editor)->deleteJson('/admin/posts/' . $this->post->id . '/lock');

        $response->assertOk();
        $this->assertDatabaseMissing('post_locks', [
            'post_id' => $this->post->id,
            'user_id' => $this->editor->id,
        ]);
    }

    public function test_show_reports_holder_without_creating_lock(): void
    {
        $this->makeLock($this->other);

        $response = $this->actingAs($this->editor)->getJson('/admin/posts/' . $this->post->id . '/lock');

        $response->assertOk();
        $response->assertJsonPath('holder.name', 'Other Person');
        $response->assertJsonPath('me', false);

        // Status check must not create a lock for the current user.
        $this->assertDatabaseMissing('post_locks', [
            'post_id' => $this->post->id,
            'user_id' => $this->editor->id,
        ]);
    }

    public function test_update_is_blocked_while_another_user_holds_a_live_lock(): void
    {
        $this->makeLock($this->other);

        $response = $this->actingAs($this->editor)->put('/admin/posts/' . $this->post->id, [
            'title' => 'Rewritten by Editor',
            'slug' => 'lockable-post',
            'content' => '<p>new body</p>',
            'status' => 'published',
        ]);

        $response->assertRedirect('/admin/posts/' . $this->post->id . '/edit');
        $response->assertSessionHas('error');

        // The post must NOT have been modified.
        $this->assertDatabaseHas('posts', [
            'id' => $this->post->id,
            'title' => 'Lockable Post',
        ]);
    }

    public function test_update_succeeds_when_lock_is_stale(): void
    {
        $this->makeLock($this->other, 'stale');

        $response = $this->actingAs($this->editor)->put('/admin/posts/' . $this->post->id, [
            'title' => 'Updated by Editor',
            'slug' => 'lockable-post',
            'content' => '<p>new body</p>',
            'status' => 'published',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'id' => $this->post->id,
            'title' => 'Updated by Editor',
        ]);
    }

    public function test_update_succeeds_with_no_lock(): void
    {
        $response = $this->actingAs($this->editor)->put('/admin/posts/' . $this->post->id, [
            'title' => 'Updated by Editor',
            'slug' => 'lockable-post',
            'content' => '<p>new body</p>',
            'status' => 'published',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'id' => $this->post->id,
            'title' => 'Updated by Editor',
        ]);
    }

    public function test_edit_page_renders_presence_badge(): void
    {
        $response = $this->actingAs($this->editor)->get('/admin/posts/' . $this->post->id . '/edit');

        $response->assertOk();
        $response->assertSee('editor-presence', false);
    }
}
