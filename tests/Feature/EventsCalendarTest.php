<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventsCalendarTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $author;
    protected Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = Site::create([
            'name' => 'Events Test Site',
            'slug' => 'events-test-site',
            'domain' => 'events.test',
        ]);

        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Admin',
            'permissions' => ['dashboard' => true, 'posts' => true],
        ]);

        $this->admin = User::factory()->create([
            'email' => 'calendar-admin@test.dev',
            'password' => bcrypt('correct-horse'),
        ]);
        $this->admin->roles()->attach($adminRole);

        $authorRole = Role::create([
            'name' => 'Editor',
            'slug' => 'editor',
            'description' => 'Editor with limited event access',
            'permissions' => ['posts' => true],
            'post_type_permissions' => [
                'event' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => false, 'publish' => false],
            ],
        ]);

        $this->author = User::factory()->create([
            'email' => 'calendar-author@test.dev',
            'password' => bcrypt('correct-horse'),
        ]);
        $this->author->roles()->attach($authorRole);
    }

    protected function createEvent(array $overrides = []): Post
    {
        return Post::create(array_merge([
            'site_id' => $this->site->id,
            'user_id' => $this->admin->id,
            'post_type' => 'event',
            'title' => 'Calendar Test Event',
            'slug' => 'calendar-test-event-' . uniqid(),
            'content' => '<p>Event body.</p>',
            'status' => 'published',
            'published_at' => now()->addDays(5),
        ], $overrides));
    }

    public function test_calendar_renders_for_admin(): void
    {
        $this->createEvent();

        $response = $this->actingAs($this->admin)->get('/admin/event/calendar');

        $response->assertOk();
        $response->assertSee('Event Calendar');
        $response->assertSee('Calendar Test Event');
    }

    public function test_calendar_renders_for_editor_role_with_event_view_permission(): void
    {
        $this->createEvent();

        $response = $this->actingAs($this->author)->get('/admin/event/calendar');

        $response->assertOk();
        $response->assertSee('Event Calendar');
    }

    public function test_calendar_forbidden_without_event_view_permission(): void
    {
        $role = $this->author->roles()->first();
        $role->update(['post_type_permissions' => [
            'event' => ['view' => false, 'create' => false, 'edit' => false, 'delete' => false, 'publish' => false],
        ]]);

        $response = $this->actingAs($this->author)->get('/admin/event/calendar');

        $response->assertForbidden();
    }

    public function test_calendar_month_navigation_changes_the_grid(): void
    {
        $this->createEvent(['published_at' => now()->addMonths(2)->startOfDay()]);

        $target = now()->addMonths(2)->format('Y-m');

        $response = $this->actingAs($this->admin)->get('/admin/event/calendar?month=' . $target);

        $response->assertOk();
        $response->assertSee('Calendar Test Event');
    }

    public function test_calendar_shows_scheduled_badge_for_future_events(): void
    {
        $this->createEvent(['published_at' => now()->addDays(3), 'status' => 'pending']);

        $response = $this->actingAs($this->admin)->get('/admin/event/calendar');

        $response->assertOk();
        $response->assertSee('Scheduled');
    }

    public function test_public_events_page_renders_upcoming_events(): void
    {
        $this->createEvent(['title' => 'Public Upcoming Event', 'published_at' => now()->addDays(2)]);

        $response = $this->get('/events');

        $response->assertOk();
        $response->assertSee('Public Upcoming Event');
    }

    public function test_public_events_page_splits_past_and_upcoming(): void
    {
        $this->createEvent([
            'title' => 'Past Event',
            'slug' => 'past-event-1',
            'published_at' => now()->subDays(10),
            'status' => 'published',
        ]);
        $this->createEvent([
            'title' => 'Upcoming Event',
            'slug' => 'upcoming-event-1',
            'published_at' => now()->addDays(5),
        ]);

        $response = $this->get('/events');

        $response->assertOk();
        $response->assertSee('Upcoming Event');
        $response->assertSee('Past Event');
        $response->assertSee('Past Events');
    }

    public function test_public_events_page_hides_unpublished_and_expired(): void
    {
        $this->createEvent([
            'title' => 'Draft Event',
            'slug' => 'draft-event-1',
            'status' => 'draft',
            'published_at' => now()->addDays(2),
        ]);
        $this->createEvent([
            'title' => 'Expired Event',
            'slug' => 'expired-event-1',
            'published_at' => now()->subDays(2),
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->get('/events');

        $response->assertOk();
        $response->assertDontSee('Draft Event');
        $response->assertDontSee('Expired Event');
    }
}
