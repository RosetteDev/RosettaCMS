<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomPostTypesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Site::create([
            'name' => 'Custom Post Types Test Site',
            'slug' => 'custom-post-types-test-site',
            'domain' => 'cpt.test',
        ]);

        $this->user = User::factory()->create([
            'email' => 'cpt-admin@test.dev',
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

    protected function createPost(string $type = 'portfolio', array $overrides = []): Post
    {
        return Post::create(array_merge([
            'site_id' => 1,
            'user_id' => $this->user->id,
            'post_type' => $type,
            'title' => ucfirst($type) . ' Test Item',
            'slug' => $type . '-test-item',
            'content' => '<p>Hello</p>',
            'status' => 'draft',
        ], $overrides));
    }

    public function test_index_renders_for_each_generic_type(): void
    {
        foreach (['portfolio', 'testimonial', 'event'] as $type) {
            $this->createPost($type);

            $response = $this->actingAs($this->user)->get('/admin/' . $type);

            $response->assertOk();
        }
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/portfolio/create');

        $response->assertOk();
        $response->assertSee('Portfolio Item');
    }

    public function test_store_creates_record_of_correct_type(): void
    {
        $response = $this->actingAs($this->user)->post('/admin/portfolio', [
            'title' => 'New Portfolio Item',
            'slug' => 'new-portfolio-item',
            'content' => '<p>Body</p>',
            'status' => 'draft',
        ]);

        $response->assertRedirect();

        $post = Post::where('post_type', 'portfolio')->where('slug', 'new-portfolio-item')->first();
        $this->assertNotNull($post);
        $this->assertSame('portfolio', $post->post_type);
    }

    public function test_edit_renders_for_own_type(): void
    {
        $post = $this->createPost('testimonial');

        $response = $this->actingAs($this->user)->get('/admin/testimonial/' . $post->id . '/edit');

        $response->assertOk();
    }

    public function test_cannot_edit_post_of_different_type(): void
    {
        $post = $this->createPost('event');

        $response = $this->actingAs($this->user)->get('/admin/portfolio/' . $post->id . '/edit');

        $response->assertNotFound();
    }

    public function test_update_saves_changes(): void
    {
        $post = $this->createPost('portfolio');

        $response = $this->actingAs($this->user)->put('/admin/portfolio/' . $post->id, [
            'title' => 'Updated Portfolio Item',
            'slug' => 'new-portfolio-item',
            'content' => '<p>Updated</p>',
            'status' => 'published',
        ]);

        $response->assertRedirect();

        $post->refresh();
        $this->assertSame('Updated Portfolio Item', $post->title);
        $this->assertSame('portfolio', $post->post_type);
    }

    public function test_destroy_deletes_record(): void
    {
        $post = $this->createPost('event');

        $response = $this->actingAs($this->user)->delete('/admin/event/' . $post->id);

        $response->assertRedirect();
        $this->assertNull(Post::find($post->id));
    }

    public function test_unknown_type_returns_404(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/unknown/');

        $response->assertNotFound();
    }
}
