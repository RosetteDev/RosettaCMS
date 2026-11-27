<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = Site::create([
            'name' => 'Bookings Test Site',
            'slug' => 'bookings-test-site',
            'domain' => 'bookings.test',
        ]);

        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Admin',
            'permissions' => ['dashboard' => true, 'posts' => true],
        ]);

        $this->admin = User::factory()->create([
            'email' => 'bookings-admin@test.dev',
            'password' => bcrypt('correct-horse'),
        ]);
        $this->admin->roles()->attach($adminRole);
    }

    protected function createBooking(array $overrides = []): Booking
    {
        return Booking::create(array_merge([
            'site_id' => $this->site->id,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'service' => 'Consultation',
            'date' => now()->addDays(3)->toDateString(),
            'time' => '10:30',
            'notes' => 'Please arrive 10 minutes early.',
            'status' => 'pending',
        ], $overrides));
    }

    public function test_public_booking_form_renders_when_enabled(): void
    {
        $response = $this->get('/book');

        $response->assertOk();
        $response->assertSee('Book an Appointment');
    }

    public function test_public_booking_form_404s_when_disabled(): void
    {
        $this->site->settings()->updateOrCreate(
            ['site_id' => $this->site->id],
            ['enable_booking_form' => false]
        );

        $response = $this->get('/book');

        $response->assertNotFound();
    }

    public function test_public_booking_submission_creates_pending_booking(): void
    {
        $response = $this->post('/book', [
            'name' => 'John Visitor',
            'email' => 'john@example.com',
            'phone' => '555-0100',
            'service' => 'Consultation',
            'date' => now()->addDays(5)->toDateString(),
            'time' => '14:00',
            'notes' => 'Please call ahead.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'name' => 'John Visitor',
            'email' => 'john@example.com',
            'service' => 'Consultation',
            'status' => 'pending',
        ]);
    }

    public function test_public_booking_submission_requires_date_and_name_and_email(): void
    {
        $response = $this->post('/book', [
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'date']);
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_public_booking_rejects_past_dates(): void
    {
        $response = $this->post('/book', [
            'name' => 'John Visitor',
            'email' => 'john@example.com',
            'date' => now()->subDay()->toDateString(),
        ]);

        $response->assertSessionHasErrors('date');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_honeypot_submission_is_silently_accepted_without_booking(): void
    {
        $response = $this->post('/book', [
            'name' => 'Bot Bot',
            'email' => 'bot@example.com',
            'date' => now()->addDays(2)->toDateString(),
            'website' => 'http://spam.example.com',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_admin_bookings_index_lists_bookings(): void
    {
        $this->createBooking(['name' => 'Listed Booking']);

        $response = $this->actingAs($this->admin)->get('/admin/bookings');

        $response->assertOk();
        $response->assertSee('Listed Booking');
        $response->assertSee('Bookings');
    }

    public function test_admin_booking_status_update_and_delete(): void
    {
        $booking = $this->createBooking();

        $response = $this->actingAs($this->admin)->post('/admin/bookings/' . $booking->id . '/status', [
            'status' => 'confirmed',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'confirmed']);

        $response = $this->actingAs($this->admin)->delete('/admin/bookings/' . $booking->id);

        $response->assertRedirect();
        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
    }

    public function test_admin_booking_status_rejects_invalid_status(): void
    {
        $booking = $this->createBooking();

        $response = $this->actingAs($this->admin)->post('/admin/bookings/' . $booking->id . '/status', [
            'status' => 'bogus',
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'pending']);
    }
}
