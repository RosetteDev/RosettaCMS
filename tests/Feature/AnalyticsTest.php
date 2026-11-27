<?php

namespace Tests\Feature;

use App\Models\PageView;
use App\Models\Post;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected Site $site;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = Site::create([
            'name' => 'Analytics Test Site',
            'slug' => 'analytics-test-site',
            'domain' => 'analytics.test',
        ]);

        $this->user = User::factory()->create([
            'email' => 'analytics-admin@test.dev',
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

    public function test_pageviews_are_recorded_for_public_gets(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $this->assertDatabaseHas('page_views', [
            'path' => '/',
        ]);
    }

    public function test_admin_routes_are_not_recorded(): void
    {
        $this->actingAs($this->user)->get('/dashboard');

        $this->assertDatabaseMissing('page_views', [
            'path' => 'dashboard',
        ]);
    }

    public function test_analytics_page_renders_for_admin(): void
    {
        PageView::create([
            'site_id' => $this->site->id,
            'path' => '/',
            'title' => 'home',
            'device' => 'desktop',
            'browser' => 'Chrome',
            'visitor_hash' => 'hash-a',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->get('/admin/analytics');
        $response->assertStatus(200);
        $response->assertSee('Analytics');
    }

    public function test_analytics_export_downloads_csv(): void
    {
        PageView::create([
            'site_id' => $this->site->id,
            'path' => '/',
            'title' => 'home',
            'device' => 'desktop',
            'browser' => 'Chrome',
            'visitor_hash' => 'hash-a',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->get('/admin/analytics/export');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_analytics_page_requires_admin(): void
    {
        $user = User::factory()->create(['email' => 'plain-user@test.dev']);

        $response = $this->actingAs($user)->get('/admin/analytics');
        $response->assertForbidden();
    }
}
