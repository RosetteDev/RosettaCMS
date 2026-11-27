<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuCustomUrlTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = Site::create([
            'name' => 'Menu Custom URL Site',
            'slug' => 'menu-custom-url-site',
        ]);

        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'permissions' => ['dashboard' => true],
        ]);

        $this->admin = User::factory()->create(['email' => 'menu-custom-url@test.dev']);
        $this->admin->roles()->attach($adminRole);
    }

    public function test_edit_page_ships_the_custom_url_toggle(): void
    {
        $menu = Menu::create([
            'site_id' => $this->site->id,
            'name' => 'Main Menu',
            'slug' => 'main-menu',
            'location' => 'header',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/menus/' . $menu->id . '/edit');

        $response->assertOk();
        $response->assertSee('data-url-picker', false);
        $response->assertSee('data-menu-type', false);
        $response->assertSee("typeSelect.value === 'custom'", false);
    }
}
