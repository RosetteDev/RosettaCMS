<?php

namespace Tests\Feature;

use App\Models\ContactSubmission;
use App\Models\Role;
use App\Models\Site;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\SpamFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Site::create(['name' => 'Test Site', 'slug' => 'test', 'domain' => 'test.test']);
        SiteSetting::create([
            'site_id' => Site::first()->id,
            'site_title' => 'Test Site',
            'admin_email' => 'admin@test.test',
        ]);

        $this->user = User::factory()->create();
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin', 'description' => '', 'permissions' => []]);
        $this->user->roles()->attach($adminRole);
    }

    public function test_honeypot_submission_is_marked_spam_and_accepted(): void
    {
        $this->post(route('contact.submit'), [
            'name' => 'Bot',
            'email' => 'bot@test.test',
            'subject' => 'spam',
            'message' => 'buy cheap meds now',
            'website' => 'http://spam.example.com',
        ])->assertRedirect();

        $submission = ContactSubmission::first();
        $this->assertNotNull($submission);
        $this->assertSame('spam', $submission->status);
        $this->assertSame(100, $submission->spam_score);
        $this->assertSame('honeypot', $submission->spam_reason);
    }

    public function test_clean_submission_is_stored_as_new(): void
    {
        $this->post(route('contact.submit'), [
            'name' => 'Jane',
            'email' => 'jane@test.test',
            'subject' => 'Hello',
            'message' => 'I would like to know more about your services.',
        ])->assertRedirect();

        $submission = ContactSubmission::first();
        $this->assertNotNull($submission);
        $this->assertSame('new', $submission->status);
        $this->assertLessThan(50, $submission->spam_score);
    }

    public function test_spam_keywords_are_scored(): void
    {
        $filter = app(SpamFilter::class);
        $result = $filter->analyze([
            'subject' => 'cheap pills',
            'message' => 'Buy cheap pills now! Limited time offer! Click here for free trial! 100% free!',
        ]);

        $this->assertGreaterThanOrEqual(50, $result['score']);
        $this->assertNotNull($result['reason']);
    }

    public function test_excessive_links_are_scored(): void
    {
        $filter = app(SpamFilter::class);
        $result = $filter->analyze([
            'subject' => '',
            'message' => 'Check http://a.com and http://b.com and http://c.com and http://d.com',
        ]);

        $this->assertGreaterThanOrEqual(40, $result['score']);
    }

    public function test_clean_message_scores_low(): void
    {
        $filter = app(SpamFilter::class);
        $result = $filter->analyze([
            'subject' => 'Pricing',
            'message' => 'Hi, could you please send me your pricing information?',
        ]);

        $this->assertLessThan(50, $result['score']);
    }

    public function test_custom_fields_are_rendered_and_stored(): void
    {
        $settings = SiteSetting::forSite(Site::first());
        $settings->update([
            'custom_form_fields' => [
                ['name' => 'company', 'label' => 'Company', 'type' => 'text', 'placeholder' => 'Acme Inc', 'required' => true],
                ['name' => 'phone', 'label' => 'Phone', 'type' => 'number', 'placeholder' => '', 'required' => false],
            ],
        ]);

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('name="company"', false)
            ->assertSee('name="phone"', false);

        $this->post(route('contact.submit'), [
            'name' => 'Jane',
            'email' => 'jane@test.test',
            'subject' => 'Hello',
            'message' => 'I would like to know more about your services.',
            'company' => 'Acme Inc',
            'phone' => '5551234',
        ])->assertRedirect();

        $submission = ContactSubmission::first();
        $this->assertSame('Acme Inc', $submission->custom_fields['company']);
        $this->assertSame('5551234', $submission->custom_fields['phone']);
    }

    public function test_required_custom_field_missing_is_rejected(): void
    {
        $settings = SiteSetting::forSite(Site::first());
        $settings->update([
            'custom_form_fields' => [
                ['name' => 'company', 'label' => 'Company', 'type' => 'text', 'placeholder' => '', 'required' => true],
            ],
        ]);

        $this->post(route('contact.submit'), [
            'name' => 'Jane',
            'email' => 'jane@test.test',
            'subject' => 'Hello',
            'message' => 'I would like to know more about your services.',
        ])->assertSessionHasErrors('company');

        $this->assertNull(ContactSubmission::first());
    }

    public function test_admin_inbox_requires_auth(): void
    {
        $this->get(route('admin.forms.index'))->assertRedirect(route('login'));
    }

    public function test_admin_inbox_lists_and_shows_submissions(): void
    {
        ContactSubmission::create([
            'site_id' => Site::first()->id,
            'name' => 'Jane',
            'email' => 'jane@test.test',
            'subject' => 'Hello',
            'message' => 'A real message.',
            'status' => 'new',
            'spam_score' => 5,
        ]);

        $this->actingAs($this->user)
            ->get(route('admin.forms.index'))
            ->assertOk()
            ->assertSee('Jane')
            ->assertSee('Hello');

        $submission = ContactSubmission::first();

        $this->actingAs($this->user)
            ->get(route('admin.forms.show', $submission))
            ->assertOk()
            ->assertSee('A real message.');

        $this->assertSame('read', $submission->fresh()->status);
    }

    public function test_status_update_and_delete_flow(): void
    {
        $submission = ContactSubmission::create([
            'site_id' => Site::first()->id,
            'name' => 'Jane',
            'email' => 'jane@test.test',
            'subject' => 'Hello',
            'message' => 'A real message.',
            'status' => 'new',
            'spam_score' => 5,
        ]);

        $this->actingAs($this->user)
            ->post(route('admin.forms.status', $submission), ['status' => 'handled'])
            ->assertRedirect();

        $this->assertSame('handled', $submission->fresh()->status);

        $this->actingAs($this->user)
            ->delete(route('admin.forms.destroy', $submission))
            ->assertRedirect();

        $this->assertNull(ContactSubmission::find($submission->id));
    }

    public function test_settings_update_saves_custom_form_fields(): void
    {
        $this->actingAs($this->user)->post(route('admin.settings.update', ['section' => 'seo']), [
            'site_name' => 'Test Site',
            'site_description' => 'A test site',
            'admin_email' => 'admin@test.test',
            'posts_per_page' => 10,
            'custom_form_fields' => json_encode([
                ['name' => 'company', 'label' => 'Company', 'type' => 'text', 'placeholder' => 'Acme', 'required' => true],
            ]),
        ])->assertRedirect();

        $settings = SiteSetting::forSite(Site::first());
        $this->assertSame('company', $settings->custom_form_fields[0]['name']);
        $this->assertSame('text', $settings->custom_form_fields[0]['type']);
        $this->assertTrue($settings->custom_form_fields[0]['required']);
    }

    public function test_settings_update_rejects_invalid_custom_form_fields(): void
    {
        $this->actingAs($this->user)->post(route('admin.settings.update', ['section' => 'seo']), [
            'site_name' => 'Test Site',
            'admin_email' => 'admin@test.test',
            'custom_form_fields' => 'not-json',
        ])->assertSessionHasErrors('custom_form_fields');

        $this->actingAs($this->user)->post(route('admin.settings.update', ['section' => 'seo']), [
            'site_name' => 'Test Site',
            'admin_email' => 'admin@test.test',
            'custom_form_fields' => json_encode([
                ['name' => 'Bad Name!', 'type' => 'text'],
            ]),
        ])->assertSessionHasErrors('custom_form_fields');
    }
}
