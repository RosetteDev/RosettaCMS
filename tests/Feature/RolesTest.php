<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        Site::create([
            'name' => 'Roles Test Site',
            'slug' => 'roles-test-site',
            'domain' => 'roles.test',
        ]);

        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Test admin',
            'permissions' => [],
        ]);

        $subscriberRole = Role::create([
            'name' => 'Subscriber',
            'slug' => 'subscriber',
            'description' => 'Test subscriber',
            'permissions' => [],
        ]);

        $this->admin = User::factory()->create([
            'email' => 'roles-admin@test.dev',
            'password' => bcrypt('correct-horse'),
        ]);
        $this->admin->roles()->attach($adminRole);

        $this->subscriber = User::factory()->create([
            'email' => 'roles-subscriber@test.dev',
            'password' => bcrypt('correct-horse'),
        ]);
        $this->subscriber->roles()->attach($subscriberRole);
    }

    public function test_index_renders_for_admin(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/roles');

        $response->assertOk();
        $response->assertSee('Roles');
    }

    public function test_non_admin_cannot_access_roles_index(): void
    {
        $response = $this->actingAs($this->subscriber)->get('/admin/roles');

        $response->assertForbidden();
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/roles/create');

        $response->assertOk();
        $response->assertSee('Create Role');
    }

    public function test_store_creates_role_with_post_type_and_field_permissions(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/roles', [
            'name' => 'Content Manager',
            'slug' => 'content-manager',
            'description' => 'Can manage posts and portfolio',
            'permissions' => ['posts' => '1'],
            'post_type_permissions' => [
                'post' => ['view' => '1', 'edit' => '1', 'publish' => '1'],
                'portfolio' => ['view' => '1', 'create' => '1'],
            ],
            'field_permissions' => [
                'post' => ['title', 'content', 'excerpt'],
            ],
        ]);

        $response->assertRedirect();

        $role = Role::where('slug', 'content-manager')->first();
        $this->assertNotNull($role);
        $this->assertSame(['view' => true, 'create' => false, 'edit' => true, 'delete' => false, 'publish' => true], $role->post_type_permissions['post']);
        $this->assertSame(['view' => true, 'create' => true, 'edit' => false, 'delete' => false, 'publish' => false], $role->post_type_permissions['portfolio']);
        $this->assertSame(['title', 'content', 'excerpt'], $role->field_permissions['post']);
    }

    public function test_edit_page_renders(): void
    {
        $role = Role::create(['name' => 'Editor', 'slug' => 'editor', 'permissions' => []]);

        $response = $this->actingAs($this->admin)->get('/admin/roles/' . $role->id . '/edit');

        $response->assertOk();
        $response->assertSee('Editor');
    }

    public function test_update_saves_changes(): void
    {
        $role = Role::create(['name' => 'Old Name', 'slug' => 'old-slug', 'permissions' => []]);

        $response = $this->actingAs($this->admin)->put('/admin/roles/' . $role->id, [
            'name' => 'New Name',
            'slug' => 'new-slug',
            'description' => 'Updated',
            'post_type_permissions' => [
                'event' => ['view' => '1'],
            ],
            'field_permissions' => [
                'event' => ['title'],
            ],
        ]);

        $response->assertRedirect();

        $role->refresh();
        $this->assertSame('New Name', $role->name);
        $this->assertSame('new-slug', $role->slug);
        $this->assertSame(['view' => true, 'create' => false, 'edit' => false, 'delete' => false, 'publish' => false], $role->post_type_permissions['event']);
        $this->assertSame(['title'], $role->field_permissions['event']);
    }

    public function test_destroy_deletes_non_default_role(): void
    {
        $role = Role::create(['name' => 'Temporary', 'slug' => 'temporary', 'permissions' => []]);

        $response = $this->actingAs($this->admin)->delete('/admin/roles/' . $role->id);

        $response->assertRedirect();
        $this->assertNull(Role::find($role->id));
    }

    public function test_destroy_blocked_for_default_roles(): void
    {
        $response = $this->actingAs($this->admin)->delete('/admin/roles/' . $this->admin->roles()->first()->id);

        $response->assertRedirect();
        $this->assertSame('admin', Role::find($this->admin->roles()->first()->id)?->slug);
    }

    public function test_role_cannot_be_deleted_when_users_assigned(): void
    {
        $role = Role::create(['name' => 'In Use', 'slug' => 'in-use', 'permissions' => []]);
        $user = User::factory()->create(['email' => 'in-use-user@test.dev']);
        $user->roles()->attach($role);

        $response = $this->actingAs($this->admin)->delete('/admin/roles/' . $role->id);

        $response->assertRedirect();
        $this->assertNotNull(Role::find($role->id));
    }
}
