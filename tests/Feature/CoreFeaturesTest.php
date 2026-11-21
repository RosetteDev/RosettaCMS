<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use App\Models\Post;
use App\Mail\ContactMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_homepage_renders_the_site_name(): void
    {
        Site::create([
            'name' => 'Rosetta Test Site',
            'slug' => 'rosetta-test-site',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Rosetta Test Site');
    }

    public function test_subscribers_are_redirected_from_the_dashboard_to_their_profile(): void
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'Subscriber',
            'slug' => 'subscriber',
            'description' => 'Public reader',
            'permissions' => [],
        ]);
        $user->roles()->attach($role);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect('/profile');

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Your Profile');
    }

    public function test_the_media_picker_requires_authentication(): void
    {
        $this->get('/admin/media/picker')->assertRedirect('/login');
    }

    public function test_a_user_can_reset_their_password_with_a_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@example.com',
            'password' => 'oldpassword',
        ]);

        $token = \Illuminate\Support\Facades\Password::createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertRedirect('/login');

        $this->assertTrue(
            \Illuminate\Support\Facades\Hash::check('newpassword123', $user->fresh()->password)
        );
    }

    public function test_the_forgot_password_page_renders(): void
    {
        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('Send Reset Link');
    }

    public function test_wordpress_shortcodes_are_expanded_by_the_content_filter(): void
    {
        $compat = app('wordpress.compat');
        $compat->addShortcode('qa_hello', function (array $attributes) {
            return 'Hello ' . ($attributes['name'] ?? 'World');
        });

        $output = $compat->applyFilters('the_content', '<p>[qa_hello name="QA"]</p>');

        $this->assertStringContainsString('Hello QA', $output);
    }

    public function test_the_contact_form_sends_a_message_to_the_site_admin(): void
    {
        Site::create([
            'name' => 'Contact Site',
            'slug' => 'contact-site',
        ]);

        Mail::fake();

        $this->post('/contact', [
            'name' => 'Reader One',
            'email' => 'reader@example.com',
            'subject' => 'Hello there',
            'message' => 'I would like to know more.',
        ])->assertRedirect();

        Mail::assertSent(ContactMessage::class, function (ContactMessage $mail) {
            return $mail->senderEmail === 'reader@example.com'
                && $mail->messageSubject === 'Hello there';
        });
    }

    public function test_the_contact_form_validates_required_fields(): void
    {
        Site::create([
            'name' => 'Contact Site',
            'slug' => 'contact-site',
        ]);

        $this->post('/contact', [])
            ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
    }

    public function test_the_contact_form_silently_rejects_honeypot_spam(): void
    {
        Site::create([
            'name' => 'Contact Site',
            'slug' => 'contact-site',
        ]);

        Mail::fake();

        $this->post('/contact', [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'subject' => 'Spam',
            'message' => 'Buy things',
            'website' => 'https://spam.example.com',
        ])->assertRedirect()->assertSessionHas('status');

        Mail::assertNothingSent();
    }

    public function test_a_scheduled_homepage_is_not_shown_before_its_publish_time(): void
    {
        $site = Site::create([
            'name' => 'Homepage Site',
            'slug' => 'homepage-site',
        ]);

        $user = User::factory()->create();

        $page = Post::create([
            'site_id' => $site->id,
            'user_id' => $user->id,
            'title' => 'Scheduled Home Page',
            'slug' => 'scheduled-home',
            'content' => '<p>Coming later.</p>',
            'post_type' => 'page',
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);

        $site->update(['home_post_id' => $page->id]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Scheduled Home Page');
    }

    public function test_missing_pages_render_the_themed_not_found_page(): void
    {
        Site::create([
            'name' => 'Rosetta Test Site',
            'slug' => 'rosetta-test-site',
        ]);

        $this->get('/a-page-that-does-not-exist')
            ->assertNotFound()
            ->assertSee('Page not found')
            ->assertSee('noindex, follow', false);
    }

    public function test_admin_post_and_page_search_filters_results(): void
    {
        $site = Site::create([
            'name' => 'Search Site',
            'slug' => 'search-site',
        ]);

        $admin = User::factory()->create();
        $role = Role::create([
            'name' => 'Administrator',
            'slug' => 'admin',
            'description' => 'Full access',
            'permissions' => [],
        ]);
        $admin->roles()->attach($role);

        $base = [
            'site_id' => $site->id,
            'user_id' => $admin->id,
            'post_type' => 'post',
            'status' => 'draft',
        ];

        Post::create(array_merge($base, ['title' => 'Alpha Post', 'slug' => 'alpha-post']));
        Post::create(array_merge($base, ['title' => 'Beta Post', 'slug' => 'beta-post']));
        Post::create(array_merge($base, ['title' => 'Alpha Page', 'slug' => 'alpha-page', 'post_type' => 'page']));

        $this->actingAs($admin)
            ->get('/admin/posts?search=Alpha')
            ->assertOk()
            ->assertSee('Alpha Post')
            ->assertDontSee('Beta Post');

        $this->actingAs($admin)
            ->get('/admin/pages?search=Alpha')
            ->assertOk()
            ->assertSee('Alpha Page')
            ->assertDontSee('Alpha Post');
    }

    public function test_pages_cannot_be_edited_through_the_posts_admin_routes(): void
    {
        $site = Site::create([
            'name' => 'Guard Site',
            'slug' => 'guard-site',
        ]);

        $admin = User::factory()->create();
        $role = Role::create([
            'name' => 'Administrator',
            'slug' => 'admin',
            'description' => 'Full access',
            'permissions' => [],
        ]);
        $admin->roles()->attach($role);

        $page = Post::create([
            'site_id' => $site->id,
            'user_id' => $admin->id,
            'title' => 'Original Page',
            'slug' => 'original-page',
            'post_type' => 'page',
            'status' => 'draft',
        ]);

        $this->actingAs($admin)
            ->get('/admin/posts/' . $page->id . '/edit')
            ->assertRedirect('/admin/posts');

        $this->actingAs($admin)
            ->put('/admin/posts/' . $page->id, [
                'title' => 'Hijacked Page',
                'status' => 'published',
            ])
            ->assertRedirect('/admin/posts');

        $this->assertSame('page', $page->fresh()->post_type);
        $this->assertSame('Original Page', $page->fresh()->title);
    }

    public function test_staff_can_preview_an_unpublished_post_but_guests_cannot(): void
    {
        $site = Site::create([
            'name' => 'Preview Site',
            'slug' => 'preview-site',
        ]);

        $admin = User::factory()->create();
        $role = Role::create([
            'name' => 'Administrator',
            'slug' => 'admin',
            'description' => 'Full access',
            'permissions' => [],
        ]);
        $admin->roles()->attach($role);

        $post = Post::create([
            'site_id' => $site->id,
            'user_id' => $admin->id,
            'title' => 'Unpublished Draft',
            'slug' => 'unpublished-draft',
            'content' => '<p>Work in progress.</p>',
            'post_type' => 'post',
            'status' => 'draft',
        ]);

        $this->get('/posts/' . $post->slug)->assertNotFound();

        $this->actingAs($admin)
            ->get('/posts/' . $post->slug . '?preview=1')
            ->assertOk()
            ->assertSee('Unpublished Draft')
            ->assertSee('not visible to visitors', false)
            ->assertSee('noindex, nofollow', false);
    }
}
