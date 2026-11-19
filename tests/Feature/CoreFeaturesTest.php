<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Site;
use App\Models\User;
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

    public function test_wordpress_shortcodes_are_expanded_by_the_content_filter(): void
    {
        $compat = app('wordpress.compat');
        $compat->addShortcode('qa_hello', function (array $attributes) {
            return 'Hello ' . ($attributes['name'] ?? 'World');
        });

        $output = $compat->applyFilters('the_content', '<p>[qa_hello name="QA"]</p>');

        $this->assertStringContainsString('Hello QA', $output);
    }
}
