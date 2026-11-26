<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Post;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiKeysTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->site = Site::create([
            'name' => 'API Test Site',
            'slug' => 'api-test-site',
            'domain' => 'api-test.test',
        ]);
    }

    public function test_api_key_is_created_with_a_hash_and_never_stored_in_plaintext(): void
    {
        $result = ApiKey::createKey($this->user->id, 'Test Key', ['posts:read']);

        $this->assertStringStartsWith('rk_', $result['plaintext']);
        $this->assertSame(strlen($result['plaintext']), strlen('rk_') + 48);

        $key = ApiKey::find($result['key']->id);
        $this->assertNotNull($key);
        $this->assertSame('Test Key', $key->name);
        $this->assertSame(['posts:read'], $key->scopes);
        $this->assertNotSame($result['plaintext'], $key->key_hash);
        $this->assertTrue(password_verify($result['plaintext'], $key->key_hash));
    }

    public function test_find_valid_returns_the_key_for_a_valid_plaintext_and_null_for_a_revoked_key(): void
    {
        $result = ApiKey::createKey($this->user->id, 'Valid Key', ['posts:read']);

        $found = ApiKey::findValid($result['plaintext']);
        $this->assertNotNull($found);
        $this->assertSame($result['key']->id, $found->id);

        $found->revoke();

        $this->assertNull(ApiKey::findValid($result['plaintext']));
    }

    public function test_find_valid_returns_null_for_an_expired_key(): void
    {
        $result = ApiKey::createKey(
            $this->user->id,
            'Expired Key',
            ['posts:read'],
            now()->subDay()
        );

        $this->assertNull(ApiKey::findValid($result['plaintext']));
    }

    public function test_write_endpoint_requires_a_key_and_the_matching_scope(): void
    {
        // No key at all → 401.
        $this->postJson('/api/v1/posts', ['title' => 'Nope'])
            ->assertUnauthorized();

        // Key without the write scope → 403.
        $readKey = ApiKey::createKey($this->user->id, 'Read only', ['posts:read'])['plaintext'];
        $this->postJson('/api/v1/posts', ['title' => 'Nope'], [
            'Authorization' => 'Bearer ' . $readKey,
        ])->assertForbidden();

        // Key with the scope → 201.
        $writeKey = ApiKey::createKey($this->user->id, 'Writer', ['posts:write'])['plaintext'];
        $this->postJson('/api/v1/posts', [
            'title' => 'API Created Post',
            'status' => 'draft',
        ], ['Authorization' => 'Bearer ' . $writeKey])
            ->assertCreated()
            ->assertJsonPath('data.title', 'API Created Post');
    }

    public function test_write_endpoint_updates_and_deletes_posts(): void
    {
        $post = $this->makePost('API Lifecycle', 'api-lifecycle');
        $key = ApiKey::createKey($this->user->id, 'Writer', ['posts:write'])['plaintext'];

        $this->putJson('/api/v1/posts/api-lifecycle', [
            'title' => 'API Lifecycle Updated',
            'status' => 'published',
        ], ['Authorization' => 'Bearer ' . $key])
            ->assertOk()
            ->assertJsonPath('data.title', 'API Lifecycle Updated');

        $this->deleteJson('/api/v1/posts/api-lifecycle-updated', [], [
            'Authorization' => 'Bearer ' . $key,
        ])->assertOk()->assertJsonPath('deleted', true);

        $this->assertDatabaseMissing('posts', ['slug' => 'api-lifecycle-updated']);
    }

    public function test_pages_write_scope_is_separate_from_posts_write_scope(): void
    {
        $key = ApiKey::createKey($this->user->id, 'Post writer', ['posts:write'])['plaintext'];

        $this->postJson('/api/v1/pages', ['title' => 'Sneaky page'], [
            'Authorization' => 'Bearer ' . $key,
        ])->assertForbidden();

        $pageKey = ApiKey::createKey($this->user->id, 'Page writer', ['pages:write'])['plaintext'];

        $this->postJson('/api/v1/pages', ['title' => 'Legit page', 'status' => 'draft'], [
            'Authorization' => 'Bearer ' . $pageKey,
        ])->assertCreated()->assertJsonPath('data.title', 'Legit page');
    }

    public function test_public_read_endpoints_work_without_a_key(): void
    {
        $this->makePost('Public Post', 'public-post');

        $this->getJson('/api/v1/posts')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'public-post');

        $this->getJson('/api/v1/posts/public-post')
            ->assertOk()
            ->assertJsonPath('data.slug', 'public-post');
    }

    public function test_admin_api_keys_index_lists_only_the_current_users_keys(): void
    {
        $otherUser = User::factory()->create();
        ApiKey::createKey($otherUser->id, 'Someone else key', ['posts:read']);
        ApiKey::createKey($this->user->id, 'Mine', ['posts:read']);

        $this->actingAs($this->user)
            ->get(route('admin.api-keys.index'))
            ->assertOk()
            ->assertSee('Mine')
            ->assertDontSee('Someone else key');
    }

    public function test_admin_api_key_creation_flow_redirects_to_show_with_plaintext(): void
    {
        $response = $this->actingAs($this->user)->post(route('admin.api-keys.store'), [
            'name' => 'New admin key',
            'scopes' => ['posts:read'],
        ]);

        $response->assertRedirect();

        $key = ApiKey::where('user_id', $this->user->id)->latest()->first();
        $this->assertNotNull($key);
        $this->assertSame('New admin key', $key->name);

        // The show page should render and contain the key prefix.
        $this->actingAs($this->user)
            ->get(route('admin.api-keys.show', $key))
            ->assertOk()
            ->assertSee($key->key_prefix);
    }

    public function test_admin_cannot_revoke_someone_elses_key(): void
    {
        $otherUser = User::factory()->create();
        $otherKey = ApiKey::createKey($otherUser->id, 'Not yours', ['posts:read'])['key'];

        $this->actingAs($this->user)
            ->delete(route('admin.api-keys.destroy', $otherKey))
            ->assertNotFound();

        $this->assertNull($otherKey->fresh()->revoked_at);
    }

    protected function makePost(string $title, string $slug): Post
    {
        return Post::create([
            'site_id' => $this->site->id,
            'user_id' => $this->user->id,
            'title' => $title,
            'slug' => $slug,
            'content' => '<p>Body.</p>',
            'post_type' => 'post',
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}
