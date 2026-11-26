<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Media;
use App\Models\Post;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use App\Models\Webhook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    protected Site $site;
    protected Site $otherSite;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin', 'permissions' => []]);
        $this->admin->roles()->attach($adminRole);

        $this->site = Site::create([
            'name' => 'Webhook Test Site',
            'slug' => 'webhook-test-site',
            'domain' => 'webhook.test',
        ]);

        $this->otherSite = Site::create([
            'name' => 'Other Site',
            'slug' => 'other-site',
            'domain' => 'other.test',
        ]);
    }

    protected function makePost(string $slug, string $status, string $type = 'post', ?int $siteId = null): Post
    {
        return Post::create([
            'site_id' => $siteId ?? $this->site->id,
            'user_id' => $this->admin->id,
            'title' => str($slug)->replace('-', ' ')->title() . ' Post',
            'slug' => $slug,
            'content' => '<p>Body</p>',
            'post_type' => $type,
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null,
        ]);
    }

    protected function makeWebhook(array $attributes = []): Webhook
    {
        return Webhook::create(array_merge([
            'site_id' => $this->site->id,
            'url' => 'https://hooks.example.com/receive',
            'secret' => 'whsec_test_secret_value',
            'events' => ['post.published', 'post.updated', 'comment.created', 'media.created'],
            'active' => true,
        ], $attributes));
    }

    public function test_create_webhook_stores_secret_and_unique_events(): void
    {
        $result = Webhook::createWebhook(
            $this->site->id,
            'https://hooks.example.com/receive',
            ['post.published', 'post.published', 'comment.created'],
            'Sync to marketing'
        );

        $webhook = $result['webhook'];

        $this->assertStringStartsWith('whsec_', $result['secret']);
        $this->assertSame($result['secret'], $webhook->secret);
        $this->assertSame(['post.published', 'comment.created'], $webhook->events);
        $this->assertSame($this->site->id, $webhook->site_id);
        $this->assertSame('Sync to marketing', $webhook->description);
        $this->assertTrue($webhook->active);
    }

    public function test_published_post_triggers_signed_delivery(): void
    {
        Http::fake(['*' => Http::response('ok', 200)]);

        $webhook = $this->makeWebhook();

        $post = $this->makePost('published-webhook-post', 'published');

        Http::assertSentCount(1);
        Http::assertSent(function ($request) use ($webhook, $post) {
            $body = $request->body();
            $expectedSignature = hash_hmac('sha256', $body, $webhook->secret);

            return $request->url() === $webhook->url
                && $request->method() === 'POST'
                && $request->header('X-Rosetta-Signature')[0] === 'sha256=' . $expectedSignature
                && $request->header('X-Rosetta-Event')[0] === 'post.published'
                && str_contains($body, 'published-webhook-post')
                && str_contains($body, 'post.published');
        });
    }

    public function test_draft_post_does_not_trigger_delivery(): void
    {
        Http::fake();

        $this->makeWebhook();
        $this->makePost('draft-webhook-post', 'draft');

        Http::assertNothingSent();
    }

    public function test_updating_a_published_post_fires_updated_event(): void
    {
        Http::fake(['*' => Http::response('ok', 200)]);

        $this->makeWebhook();

        $post = $this->makePost('updatable-webhook-post', 'published');
        $this->assertCount(1, Http::recorded());

        $post->title = 'New Title';
        $post->save();

        $this->assertCount(2, Http::recorded());
        $events = Http::recorded()->map(fn ($pair) => $pair[0]->header('X-Rosetta-Event')[0])->all();
        $this->assertSame(['post.published', 'post.updated'], $events);
    }

    public function test_disabled_webhook_does_not_receive_events(): void
    {
        Http::fake();

        $this->makeWebhook(['active' => false]);
        $this->makePost('disabled-webhook-post', 'published');

        Http::assertNothingSent();
    }

    public function test_webhook_scoped_to_its_site_only(): void
    {
        Http::fake(['*' => Http::response('ok', 200)]);

        $this->makeWebhook();

        $this->makePost('other-site-post', 'published', 'post', $this->otherSite->id);

        Http::assertNothingSent();

        $this->makePost('own-site-post', 'published', 'post', $this->site->id);

        Http::assertSentCount(1);
    }

    public function test_site_agnostic_webhook_fires_for_any_site(): void
    {
        Http::fake(['*' => Http::response('ok', 200)]);

        $this->makeWebhook(['site_id' => null]);

        $this->makePost('other-site-post-2', 'published', 'post', $this->otherSite->id);

        Http::assertSentCount(1);
    }

    public function test_comment_created_triggers_delivery(): void
    {
        Http::fake(['*' => Http::response('ok', 200)]);

        $this->makeWebhook();
        $post = $this->makePost('comment-target-post', 'published');

        // The post itself fires post.published (1 delivery).
        $this->assertCount(1, Http::recorded());

        Comment::create([
            'post_id' => $post->id,
            'content' => 'Nice article!',
            'author_name' => 'Reader',
            'author_email' => 'reader@example.com',
            'status' => 'pending',
        ]);

        $this->assertCount(2, Http::recorded());
        $events = Http::recorded()->map(fn ($pair) => $pair[0]->header('X-Rosetta-Event')[0])->all();
        $this->assertContains('comment.created', $events);
    }

    public function test_media_created_triggers_delivery(): void
    {
        Http::fake(['*' => Http::response('ok', 200)]);

        $this->makeWebhook();

        Media::create([
            'site_id' => $this->site->id,
            'user_id' => $this->admin->id,
            'file_name' => 'photo.jpg',
            'file_path' => 'media/photo.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 12345,
        ]);

        Http::assertSentCount(1);
        Http::assertSent(function ($request) {
            return $request->header('X-Rosetta-Event')[0] === 'media.created'
                && str_contains($request->body(), 'photo.jpg');
        });
    }

    public function test_delivery_records_failure_without_throwing(): void
    {
        Http::fake(['*' => Http::response('bad', 500)]);

        $webhook = $this->makeWebhook();
        $post = $this->makePost('failing-webhook-post', 'published');

        $webhook->refresh();

        $this->assertSame(500, $webhook->last_response_code);
        $this->assertNotNull($webhook->last_fired_at);
        $this->assertNotNull($webhook->last_error);
    }

    public function test_admin_can_create_webhook(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/webhooks', [
            'url' => 'https://hooks.example.com/receive',
            'description' => 'Marketing sync',
            'events' => ['post.published', 'comment.created'],
        ]);

        $response->assertRedirect(route('admin.webhooks.index'));

        $webhook = Webhook::where('url', 'https://hooks.example.com/receive')->first();

        $this->assertNotNull($webhook);
        $this->assertSame($this->site->id, $webhook->site_id);
        $this->assertSame(['post.published', 'comment.created'], $webhook->events);
        $this->assertTrue($webhook->active);
        $this->assertNotNull($webhook->secret);
    }

    public function test_admin_create_webhook_validates_input(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/webhooks', [
            'url' => 'not-a-url',
            'events' => ['not.an.event'],
        ]);

        $response->assertSessionHasErrors(['url', 'events.0']);
        $this->assertSame(0, Webhook::count());
    }

    public function test_admin_can_test_webhook(): void
    {
        Http::fake(['*' => Http::response('ok', 200)]);

        $webhook = $this->makeWebhook();

        $response = $this->actingAs($this->admin)->post("/admin/webhooks/{$webhook->id}/test");

        $response->assertRedirect(route('admin.webhooks.index'));
        $response->assertSessionHas('success');
        $webhook->refresh();
        $this->assertSame(200, $webhook->last_response_code);
    }

    public function test_admin_can_delete_webhook(): void
    {
        $webhook = $this->makeWebhook();

        $response = $this->actingAs($this->admin)->delete("/admin/webhooks/{$webhook->id}");

        $response->assertRedirect(route('admin.webhooks.index'));
        $this->assertNull(Webhook::find($webhook->id));
    }

    public function test_non_admin_cannot_manage_webhooks(): void
    {
        $editor = User::factory()->create();

        $response = $this->actingAs($editor)->get('/admin/webhooks');

        $this->assertTrue(in_array($response->status(), [403, 302, 401]));
    }
}
