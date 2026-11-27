<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Site::create([
            'name' => 'Perf Test Site',
            'slug' => 'perf-test-site',
            'domain' => 'perf-test.test',
        ]);

        $this->user = User::factory()->create([
            'email' => 'perf-test@test.dev',
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

    public function test_full_page_cache_hit_on_second_request(): void
    {
        $first = $this->get('/');
        $first->assertStatus(200);
        $this->assertSame('MISS', $first->headers->get('X-Full-Page-Cache'));

        $second = $this->get('/');
        $second->assertStatus(200);
        $this->assertSame('HIT', $second->headers->get('X-Full-Page-Cache'));
    }

    public function test_full_page_cache_invalidates_on_content_bump(): void
    {
        $this->get('/');

        \App\Services\ContentCache::bump();

        $response = $this->get('/');
        $this->assertSame('MISS', $response->headers->get('X-Full-Page-Cache'));
    }

    public function test_authenticated_requests_are_not_full_page_cached(): void
    {
        $response = $this->actingAs($this->user)->get('/');

        // Authenticated responses must never carry the HIT/MISS marker.
        $this->assertNull($response->headers->get('X-Full-Page-Cache'));
    }

    public function test_build_assets_get_immutable_cache_headers(): void
    {
        $response = $this->get('/build/assets/nonexistent.js');

        // Middleware sets the header even for 404s from the static server path.
        $this->assertStringContainsString('immutable', $response->headers->get('Cache-Control') ?? '');
    }

    public function test_dashboard_shows_query_profiler(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Query Profiler', false);
    }

    public function test_home_page_cache_is_versioned_by_content_version(): void
    {
        $response = $this->get('/');

        $this->assertSame('MISS', $response->headers->get('X-Full-Page-Cache'));

        // Bump version, then re-fetch — must be a fresh MISS again.
        \App\Services\ContentCache::bump();
        $response = $this->get('/');
        $this->assertSame('MISS', $response->headers->get('X-Full-Page-Cache'));
    }

    public function test_content_bump_changes_cache_key_version(): void
    {
        $versionBefore = \Cache::get('content_version', 0);

        \App\Services\ContentCache::bump();

        $versionAfter = \Cache::get('content_version', 0);
        $this->assertNotSame($versionBefore, $versionAfter);
    }
}
