<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Role;
use App\Models\Site;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WordPressCompatTest extends TestCase
{
    use RefreshDatabase;

    protected Site $site;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = Site::create([
            'name' => 'Compat Site',
            'slug' => 'compat-site',
        ]);

        $this->user = User::factory()->create(['name' => 'Compat Author']);
    }

    public function test_post_helpers_work_with_rosetta_posts(): void
    {
        $post = $this->makePost();

        $this->assertSame($post->id, get_post($post->id)->id);
        $this->assertSame('Compat Article', get_the_title($post));
        $this->assertSame('post', get_post_type($post));
        $this->assertStringContainsString('/posts/compat-article', get_permalink($post));
    }

    public function test_post_meta_can_be_created_read_and_deleted(): void
    {
        $post = $this->makePost();

        $this->assertTrue(update_post_meta($post->id, 'custom_key', 'custom value'));
        $this->assertSame('custom value', get_post_meta($post->id, 'custom_key', true));
        $this->assertSame(['custom value'], get_post_meta($post->id, 'custom_key'));
        $this->assertTrue(metadata_exists('post', $post->id, 'custom_key'));

        $this->assertTrue(delete_post_meta($post->id, 'custom_key'));
        $this->assertSame('', get_post_meta($post->id, 'custom_key', true));
        $this->assertFalse(metadata_exists('post', $post->id, 'custom_key'));

        $this->assertTrue(add_post_meta($post->id, 'unique_key', 'one', true));
        $this->assertFalse(add_post_meta($post->id, 'unique_key', 'two', true));
        $this->assertSame('one', get_post_meta($post->id, 'unique_key', true));
    }

    public function test_hook_lookup_reflects_registered_callbacks(): void
    {
        $action = function () {
            return true;
        };
        $filter = function ($value) {
            return $value;
        };

        add_action('qa_action', $action);
        add_filter('qa_filter', $filter);

        $this->assertTrue(has_action('qa_action'));
        $this->assertTrue(has_action('qa_action', $action));
        $this->assertFalse(has_action('missing_action'));
        $this->assertTrue(has_filter('qa_filter'));
        $this->assertTrue(has_filter('qa_filter', $filter));
        $this->assertFalse(has_filter('missing_filter'));
    }

    public function test_bloginfo_and_current_user_expose_site_and_role_data(): void
    {
        SiteSetting::create([
            'site_id' => $this->site->id,
            'site_title' => 'Compat Site Title',
            'admin_email' => 'owner@example.com',
        ]);

        $this->assertSame('Compat Site Title', get_bloginfo('name'));
        $this->assertSame('owner@example.com', get_bloginfo('admin_email'));

        $role = Role::create([
            'name' => 'Editor',
            'slug' => 'editor',
            'description' => 'Editor',
            'permissions' => [],
        ]);
        $this->user->roles()->attach($role);

        $this->actingAs($this->user);

        $current = wp_get_current_user();
        $this->assertSame($this->user->email, $current->user_email);
        $this->assertContains('editor', $current->roles);
    }

    public function test_is_single_and_is_page_follow_the_current_route(): void
    {
        $post = $this->makePost();
        $page = $this->makePost([
            'title' => 'Compat Page',
            'slug' => 'compat-page',
            'post_type' => 'page',
        ]);

        $this->get('/posts/' . $post->slug)->assertOk();
        $this->assertTrue(is_single());
        $this->assertFalse(is_page());
        $this->assertTrue(is_singular());

        $this->get('/pages/' . $page->slug)->assertOk();
        $this->assertTrue(is_page());
        $this->assertFalse(is_single());
        $this->assertTrue(is_singular());
    }

    public function test_wp_mail_delegates_to_the_mailer(): void
    {
        Mail::fake();

        $this->assertTrue(wp_mail(
            'reader@example.com',
            'A compatible subject',
            'A compatible message',
            ['From: Compat Site <hello@example.com>', 'Reply-To: reply@example.com']
        ));
    }

    protected function makePost(array $attributes = []): Post
    {
        return Post::create(array_merge([
            'site_id' => $this->site->id,
            'user_id' => $this->user->id,
            'title' => 'Compat Article',
            'slug' => 'compat-article',
            'content' => '<p>Compat body.</p>',
            'post_type' => 'post',
            'status' => 'published',
            'published_at' => now(),
        ], $attributes));
    }
}
