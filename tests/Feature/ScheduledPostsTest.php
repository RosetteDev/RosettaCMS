<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduledPostsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->site = Site::create([
            'name' => 'Schedule Test Site',
            'slug' => 'schedule-test-site',
            'domain' => 'schedule.test',
        ]);
    }

    public function test_post_with_future_date_is_scheduled_not_published(): void
    {
        $post = Post::create([
            'site_id' => $this->site->id,
            'user_id' => $this->user->id,
            'title' => 'Future Post',
            'slug' => 'future-post',
            'content' => '<p>Body.</p>',
            'post_type' => 'post',
            'status' => 'pending',
            'published_at' => now()->addHour(),
        ]);

        $this->assertTrue($post->isScheduled());
        $this->assertFalse($post->published_at->isPast());
    }

    public function test_publish_due_command_publishes_scheduled_posts(): void
    {
        $post = Post::create([
            'site_id' => $this->site->id,
            'user_id' => $this->user->id,
            'title' => 'Due Post',
            'slug' => 'due-post',
            'content' => '<p>Body.</p>',
            'post_type' => 'post',
            'status' => 'pending',
            'published_at' => now()->subMinute(),
        ]);

        $this->artisan('posts:publish-due')->assertSuccessful();

        $this->assertSame('published', $post->fresh()->status);
    }

    public function test_publish_due_command_leaves_future_scheduled_posts_pending(): void
    {
        $post = Post::create([
            'site_id' => $this->site->id,
            'user_id' => $this->user->id,
            'title' => 'Future Scheduled Post',
            'slug' => 'future-scheduled-post',
            'content' => '<p>Body.</p>',
            'post_type' => 'post',
            'status' => 'pending',
            'published_at' => now()->addHour(),
        ]);

        $this->artisan('posts:publish-due')->assertSuccessful();

        $this->assertSame('pending', $post->fresh()->status);
    }

    public function test_scheduled_posts_do_not_appear_on_the_public_site(): void
    {
        $future = Post::create([
            'site_id' => $this->site->id,
            'user_id' => $this->user->id,
            'title' => 'Hidden Scheduled Post',
            'slug' => 'hidden-scheduled-post',
            'content' => '<p>Body.</p>',
            'post_type' => 'post',
            'status' => 'pending',
            'published_at' => now()->addHour(),
        ]);

        $this->get('/posts/hidden-scheduled-post')->assertNotFound();
    }

    public function test_save_post_with_future_date_forces_pending_status(): void
    {
        $role = Role::create(['name' => 'Editor', 'slug' => 'editor', 'permissions' => []]);
        $this->user->roles()->attach($role);
        $this->actingAs($this->user);

        $this->post(route('admin.posts.store'), [
            'title' => 'Scheduled via Admin',
            'slug' => 'scheduled-via-admin',
            'content' => '<p>Body.</p>',
            'status' => 'published',
            'published_at' => now()->addDay()->format('Y-m-d\TH:i'),
        ]);

        $post = Post::where('slug', 'scheduled-via-admin')->first();
        $this->assertNotNull($post);
        $this->assertSame('pending', $post->status);
        $this->assertTrue($post->published_at->isFuture());
    }
}
