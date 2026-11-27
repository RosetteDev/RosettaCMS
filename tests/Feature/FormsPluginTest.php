<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormsPluginTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = Site::create([
            'name' => 'Form Test Site',
            'slug' => 'form-test-site',
            'domain' => 'forms.test',
        ]);

        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Admin',
            'permissions' => ['dashboard' => true, 'posts' => true],
        ]);

        $this->admin = User::factory()->create([
            'email' => 'forms-admin@test.dev',
            'password' => bcrypt('correct-horse'),
        ]);
        $this->admin->roles()->attach($adminRole);
    }

    protected function makeForm(array $overrides = []): Form
    {
        return Form::create(array_merge([
            'site_id' => $this->site->id,
            'user_id' => $this->admin->id,
            'name' => 'Contact Form',
            'slug' => 'contact',
            'description' => 'A test form',
            'fields' => [
                ['name' => 'full_name', 'label' => 'Full Name', 'type' => 'text', 'required' => true, 'placeholder' => null, 'options' => null, 'help_text' => null],
                ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'placeholder' => null, 'options' => null, 'help_text' => null],
                ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => false, 'placeholder' => null, 'options' => null, 'help_text' => null],
            ],
            'settings' => [
                'submit_label' => 'Send',
                'success_message' => 'Thanks! We got it.',
                'mail_to' => null,
                'spam_protection' => true,
                'confirm_message' => null,
            ],
            'enabled' => true,
        ], $overrides));
    }

    // ---------- Plugin registration ----------

    public function test_forms_plugin_is_registered_and_enabled(): void
    {
        $this->assertTrue(app()->bound('forms.service'));
        $this->assertSame('Forms', app('plugin.manager')->scan()['Forms']->getName());
    }

    // ---------- Public rendering ----------

    public function test_public_form_renders_for_enabled_form(): void
    {
        $form = $this->makeForm();

        $response = $this->get('/forms/' . $form->slug);

        $response->assertOk();
        $response->assertSee('Contact Form');
        $response->assertSee('name="full_name"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="message"', false);
    }

    public function test_public_form_404s_for_unknown_slug(): void
    {
        $this->get('/forms/does-not-exist')->assertNotFound();
    }

    public function test_public_form_404s_when_disabled(): void
    {
        $form = $this->makeForm(['enabled' => false]);

        $this->get('/forms/' . $form->slug)->assertNotFound();
    }

    // ---------- Public submission ----------

    public function test_public_submission_creates_a_submission(): void
    {
        $form = $this->makeForm();

        $response = $this->post('/forms/' . $form->slug, [
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'message' => 'Hello there',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('form_submissions', [
            'form_id' => $form->id,
            'status' => 'new',
        ]);

        $submission = FormSubmission::where('form_id', $form->id)->first();
        $this->assertSame('Jane Doe', $submission->data['full_name']);
        $this->assertSame('jane@example.com', $submission->data['email']);
        $this->assertSame('Hello there', $submission->data['message']);
    }

    public function test_public_submission_requires_required_fields(): void
    {
        $form = $this->makeForm();

        $response = $this->from('/forms/' . $form->slug)->post('/forms/' . $form->slug, [
            'full_name' => '',
            'email' => 'not-an-email',
        ]);

        $response->assertRedirect('/forms/' . $form->slug);
        $response->assertSessionHasErrors(['full_name', 'email']);
        $this->assertDatabaseCount('form_submissions', 0);
    }

    public function test_public_submission_honeypot_silently_accepts_bots(): void
    {
        $form = $this->makeForm();

        $response = $this->post('/forms/' . $form->slug, [
            'full_name' => 'Bot',
            'email' => 'bot@example.com',
            'website' => 'http://spam.example', // honeypot
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $submission = FormSubmission::where('form_id', $form->id)->first();
        $this->assertSame('spam', $submission->status);
        $this->assertSame(100, $submission->spam_score);
    }

    public function test_public_submission_marks_spam_and_skips_mail(): void
    {
        $form = $this->makeForm();

        $this->post('/forms/' . $form->slug, [
            'full_name' => 'Spam Bot',
            'email' => 'spam@example.com',
            'message' => 'buy cheap pills now http://spam.example http://spam2.example http://spam3.example http://spam4.example',
        ]);

        $submission = FormSubmission::where('form_id', $form->id)->first();
        $this->assertSame('spam', $submission->status);
        $this->assertGreaterThanOrEqual(50, $submission->spam_score);
    }

    public function test_public_submission_respects_spam_protection_toggle(): void
    {
        $form = $this->makeForm([
            'settings' => [
                'submit_label' => 'Send',
                'success_message' => 'Thanks!',
                'mail_to' => null,
                'spam_protection' => false,
                'confirm_message' => null,
            ],
        ]);

        $this->post('/forms/' . $form->slug, [
            'full_name' => 'Spam Bot',
            'email' => 'spam@example.com',
            'message' => 'buy cheap pills http://spam.example http://spam2.example http://spam3.example',
        ]);

        $submission = FormSubmission::where('form_id', $form->id)->first();
        $this->assertSame('new', $submission->status);
        $this->assertSame(0, $submission->spam_score);
    }

    // ---------- Admin ----------

    public function test_admin_forms_builder_lists_forms(): void
    {
        $this->makeForm();

        $response = $this->actingAs($this->admin)->get('/admin/forms-builder');

        $response->assertOk();
        $response->assertSee('Contact Form');
    }

    public function test_admin_forms_builder_requires_auth(): void
    {
        $this->get('/admin/forms-builder')->assertRedirect('/login');
    }

    public function test_admin_can_create_a_form(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/forms-builder', [
            'name' => 'Newsletter Signup',
            'description' => 'Subscribe to our newsletter',
            'enabled' => '1',
            'fields' => [
                ['label' => 'Email', 'type' => 'email', 'required' => '1'],
                ['label' => 'Topics', 'type' => 'select', 'options' => "Updates\nNews\nEvents"],
            ],
            'settings' => [
                'submit_label' => 'Subscribe',
                'success_message' => 'You are subscribed!',
                'mail_to' => 'admin@example.com',
                'spam_protection' => '1',
            ],
        ]);

        $response->assertRedirect(route('admin.forms-builder.index'));

        $this->assertDatabaseHas('forms', [
            'name' => 'Newsletter Signup',
            'slug' => 'newsletter-signup',
            'enabled' => true,
        ]);

        $form = Form::where('slug', 'newsletter-signup')->first();
        $this->assertCount(2, $form->fields);
        $this->assertSame(['Updates', 'News', 'Events'], $form->fields[1]['options']);
        $this->assertSame('Subscribe', $form->setting('submit_label'));
    }

    public function test_admin_create_form_validates_required_name(): void
    {
        $response = $this->actingAs($this->admin)->from('/admin/forms-builder/create')->post('/admin/forms-builder', [
            'name' => '',
            'fields' => [['label' => 'Email', 'type' => 'email']],
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseCount('forms', 0);
    }

    public function test_admin_can_update_a_form(): void
    {
        $form = $this->makeForm();

        $response = $this->actingAs($this->admin)->put('/admin/forms-builder/' . $form->id, [
            'name' => 'Renamed Form',
            'slug' => 'renamed-form',
            'enabled' => '1',
            'fields' => [
                ['label' => 'Email', 'type' => 'email', 'required' => '1'],
            ],
            'settings' => [
                'submit_label' => 'Send',
                'success_message' => 'Thanks!',
                'spam_protection' => '1',
            ],
        ]);

        $response->assertRedirect(route('admin.forms-builder.index'));
        $this->assertDatabaseHas('forms', ['id' => $form->id, 'name' => 'Renamed Form', 'slug' => 'renamed-form']);
    }

    public function test_admin_can_delete_a_form_and_its_submissions(): void
    {
        $form = $this->makeForm();
        FormSubmission::create([
            'site_id' => $this->site->id,
            'form_id' => $form->id,
            'data' => ['full_name' => 'Jane'],
            'status' => 'new',
        ]);

        $response = $this->actingAs($this->admin)->delete('/admin/forms-builder/' . $form->id);

        $response->assertRedirect(route('admin.forms-builder.index'));
        $this->assertSoftDeleted('forms', ['id' => $form->id]);
        $this->assertDatabaseMissing('form_submissions', ['form_id' => $form->id]);
    }

    public function test_admin_submissions_inbox_lists_and_filters_submissions(): void
    {
        $form = $this->makeForm();
        FormSubmission::create([
            'site_id' => $this->site->id,
            'form_id' => $form->id,
            'data' => ['full_name' => 'Jane Doe', 'email' => 'jane@example.com'],
            'status' => 'new',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/forms-builder/' . $form->id . '/submissions');

        $response->assertOk();
        $response->assertSee('Jane Doe');

        $response = $this->actingAs($this->admin)->get('/admin/forms-builder/' . $form->id . '/submissions?status=spam');
        $response->assertOk();
        $response->assertDontSee('Jane Doe');
    }

    public function test_admin_submission_detail_shows_data_and_marks_read(): void
    {
        $form = $this->makeForm();
        $submission = FormSubmission::create([
            'site_id' => $this->site->id,
            'form_id' => $form->id,
            'data' => ['full_name' => 'Jane Doe', 'email' => 'jane@example.com', 'message' => 'Hello'],
            'status' => 'new',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/forms-builder/' . $form->id . '/submissions/' . $submission->id);

        $response->assertOk();
        $response->assertSee('Jane Doe');

        $submission->refresh();
        $this->assertSame('read', $submission->status);
    }

    public function test_admin_can_update_submission_status(): void
    {
        $form = $this->makeForm();
        $submission = FormSubmission::create([
            'site_id' => $this->site->id,
            'form_id' => $form->id,
            'data' => ['full_name' => 'Jane Doe'],
            'status' => 'new',
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/forms-builder/' . $form->id . '/submissions/' . $submission->id . '/status', [
            'status' => 'handled',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('form_submissions', ['id' => $submission->id, 'status' => 'handled']);
    }

    public function test_admin_cannot_touch_another_site_form(): void
    {
        $otherSite = Site::create(['name' => 'Other', 'slug' => 'other-site']);
        $foreign = Form::create([
            'site_id' => $otherSite->id,
            'name' => 'Foreign Form',
            'slug' => 'foreign-form',
            'fields' => [['name' => 'x', 'label' => 'X', 'type' => 'text', 'required' => false]],
            'enabled' => true,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/forms-builder/' . $foreign->id . '/edit');

        $response->assertNotFound();
    }
}
