<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduledAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->site = Site::create([
            'name' => 'Automation Test Site',
            'slug' => 'automation-test-site',
            'domain' => 'automation.test',
        ]);
    }

    protected function createPost(array $overrides = []): Post
    {
        return Post::create(array_merge([
            'site_id' => $this->site->id,
            'user_id' => $this->user->id,
            'title' => 'Automation Post',
            'slug' => 'automation-post-' . uniqid(),
            'content' => '<p>Body.</p>',
            'post_type' => 'post',
            'status' => 'published',
            'published_at' => now(),
        ], $overrides));
    }

    public function test_is_expired_reflects_expiry_date(): void
    {
        $expired = $this->createPost(['expires_at' => now()->subHour()]);
        $this->assertTrue($expired->isExpired());

        $future = $this->createPost(['expires_at' => now()->addHour()]);
        $this->assertFalse($future->isExpired());

        $none = $this->createPost();
        $this->assertFalse($none->isExpired());
    }

    public function test_published_scope_excludes_expired_posts(): void
    {
        $this->createPost(['expires_at' => now()->subMinute()]);

        $this->assertSame(0, Post::where('site_id', $this->site->id)->published()->count());
    }

    public function test_posts_expire_command_unpublishes_expired_posts(): void
    {
        $post = $this->createPost(['expires_at' => now()->subMinute()]);

        $this->artisan('posts:expire')->assertSuccessful();

        $this->assertSame('draft', $post->fresh()->status);

        // ...and the next publish-due run must not re-publish it.
        $this->artisan('posts:publish-due')->assertSuccessful();
        $this->assertSame('draft', $post->fresh()->status);
    }

    public function test_posts_expire_command_leaves_unexpired_posts_alone(): void
    {
        $post = $this->createPost(['expires_at' => now()->addHour()]);

        $this->artisan('posts:expire')->assertSuccessful();

        $this->assertSame('published', $post->fresh()->status);
    }

    public function test_recurring_post_is_re_armed_after_publish_due(): void
    {
        $post = Post::create([
            'site_id' => $this->site->id,
            'user_id' => $this->user->id,
            'title' => 'Recurring Post',
            'slug' => 'recurring-post',
            'content' => '<p>Body.</p>',
            'post_type' => 'post',
            'status' => 'pending',
            'published_at' => now()->subMinute(),
            'repeat_frequency' => 'daily',
        ]);

        $this->artisan('posts:publish-due')->assertSuccessful();

        $fresh = $post->fresh();
        // Re-armed: back to pending with a future publish date.
        $this->assertSame('pending', $fresh->status);
        $this->assertTrue($fresh->published_at->isFuture());
    }

    public function test_recurring_post_not_re_armed_after_repeat_until(): void
    {
        $post = Post::create([
            'site_id' => $this->site->id,
            'user_id' => $this->user->id,
            'title' => 'Recurring Post Expired',
            'slug' => 'recurring-post-expired',
            'content' => '<p>Body.</p>',
            'post_type' => 'post',
            'status' => 'pending',
            'published_at' => now()->subMinute(),
            'repeat_frequency' => 'daily',
            'repeat_until' => now()->subDay()->toDateString(),
        ]);

        $this->artisan('posts:publish-due')->assertSuccessful();

        $fresh = $post->fresh();
        // Window ended: published, no re-arm.
        $this->assertSame('published', $fresh->status);
    }

    public function test_next_occurrence_respects_repeat_until(): void
    {
        $post = $this->createPost([
            'repeat_frequency' => 'weekly',
            'repeat_until' => now()->subDay(),
        ]);

        $this->assertNull($post->nextOccurrence());

        $post->repeat_until = now()->addMonth();
        $this->assertNotNull($post->nextOccurrence());
    }

    public function test_posts_purge_deletes_old_trashed_posts_only(): void
    {
        $old = $this->createPost(['status' => 'trash']);
        $recent = $this->createPost(['status' => 'trash']);
        $published = $this->createPost(['status' => 'published']);

        Post::where('id', $old->id)->update(['updated_at' => now()->subDays(40)]);
        Post::where('id', $recent->id)->update(['updated_at' => now()->subDays(2)]);
        Post::where('id', $published->id)->update(['updated_at' => now()->subDays(90)]);

        $this->artisan('posts:purge', ['--days' => 30])->assertSuccessful();

        $this->assertNull(Post::find($old->id));
        $this->assertNotNull(Post::find($recent->id));
        $this->assertNotNull(Post::find($published->id));
    }

    public function test_posts_digest_sends_email_for_recently_published_posts(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $this->createPost(['published_at' => now()->subHours(2)]);
        $this->createPost(['slug' => 'old-post', 'published_at' => now()->subHours(48)]);

        $this->artisan('posts:digest', ['--hours' => 24])->assertSuccessful();

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\PostDigest::class, function ($mail) {
            return $mail->posts->contains('title', 'Automation Post');
        });
    }

    public function test_posts_digest_skips_when_nothing_published(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $this->artisan('posts:digest')->assertSuccessful();

        \Illuminate\Support\Facades\Mail::assertNothingSent();
    }

    public function test_admin_form_saves_expiry_and_repeat_fields(): void
    {
        $role = Role::create(['name' => 'Editor', 'slug' => 'editor', 'permissions' => ['posts' => true]]);
        $this->user->roles()->attach($role);
        $this->actingAs($this->user);

        $this->post(route('admin.posts.store'), [
            'title' => 'Expiring Post',
            'slug' => 'expiring-post',
            'content' => '<p>Body.</p>',
            'status' => 'published',
            'expires_at' => now()->addDays(3)->format('Y-m-d\TH:i'),
            'repeat_frequency' => 'weekly',
            'repeat_until' => now()->addMonth()->toDateString(),
        ]);

        $post = Post::where('slug', 'expiring-post')->first();
        $this->assertNotNull($post);
        $this->assertNotNull($post->expires_at);
        $this->assertSame('weekly', $post->repeat_frequency);
        $this->assertNotNull($post->repeat_until);
    }
}
