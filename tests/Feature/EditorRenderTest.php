<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditorRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_form_renders_custom_fields_and_toolbar(): void
    {
        Site::create(['name' => 'Editor Test Site', 'slug' => 'editor-test-site', 'domain' => 'editor.test']);

        $user = User::factory()->create(['email' => 'editor@test.dev', 'password' => bcrypt('correct-horse')]);
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin', 'description' => 'Test', 'permissions' => []]);
        $user->roles()->attach($role);

        $response = $this->actingAs($user)->get('/admin/posts/create');

        $response->assertStatus(200);
        $response->assertSee('Custom Fields');
        $response->assertSee('id="custom-fields"', false);
        $response->assertSee('font: []', false);
        $response->assertSee('color: []', false);
    }
}
