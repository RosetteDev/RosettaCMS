<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Site;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected Site $site;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = Site::create([
            'name' => 'API Test Site',
            'slug' => 'api-test-site',
            'description' => 'Testing the REST API',
        ]);

        $this->user = User::factory()->create(['name' => 'API Author']);
    }

    public function test_the_api_index_describes_the_site_and_endpoints(): void
    {
        $this->getJson('/api')
            ->assertOk()
            ->assertJsonPath('name', 'API Test Site')
            ->assertJsonPath('version', 'v1')
            ->assertJsonStructure([
                'endpoints' => ['posts', 'pages', 'categories', 'tags', 'search'],
            ]);
    }

    public function test_the_post_listing_only_returns_published_content(): void
    {
        $this->makePost('published-post', 'published', 'post');
        $this->makePost('draft-post', 'draft', 'post');

        $response = $this->getJson('/api/posts?per_page=10')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', 'published-post');

        $this->assertCount(1, $response->json('data'));
    }

    public function test_drafts_are_not_available_through_single_endpoints(): void
    {
        $this->makePost('hidden-draft', 'draft', 'post');
        $this->makePost('hidden-page', 'draft', 'page');

        $this->getJson('/api/posts/hidden-draft')->assertNotFound();
        $this->getJson('/api/pages/hidden-page')->assertNotFound();
        $this->getJson('/wp-json/wp/v2/posts/hidden-draft')->assertNotFound();
    }

    public function test_a_published_post_exposes_categories_tags_and_seo(): void
    {
        $category = Category::create(['name' => 'News', 'slug' => 'news']);
        $tag = Tag::create(['name' => 'Laravel', 'slug' => 'laravel']);

        $post = $this->makePost('hello-api', 'published', 'post', [
            'title' => 'Hello API Post',
            'meta' => [
                'seo_title' => 'Hello API',
                'seo_description' => 'A short API description.',
                'robots' => 'noindex',
            ],
        ]);

        $post->categories()->attach($category);
        $post->tags()->attach($tag);

        $this->getJson('/api/posts/hello-api')
            ->assertOk()
            ->assertJsonPath('data.title', 'Hello API Post')
            ->assertJsonPath('data.categories.0.slug', 'news')
            ->assertJsonPath('data.tags.0.slug', 'laravel')
            ->assertJsonPath('data.seo.title', 'Hello API')
            ->assertJsonPath('data.seo.robots', 'noindex');
    }

    public function test_listings_can_be_filtered_by_category(): void
    {
        $category = Category::create(['name' => 'Guides', 'slug' => 'guides']);

        $this->makePost('guide-one', 'published', 'post')->categories()->attach($category);
        $this->makePost('other-one', 'published', 'post');

        $this->getJson('/api/posts?category=guides')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', 'guide-one');
    }

    public function test_the_wordpress_endpoint_returns_compatible_fields_and_totals(): void
    {
        $this->makePost('wp-one', 'published', 'post');
        $this->makePost('wp-two', 'published', 'post');

        $response = $this->getJson('/wp-json/wp/v2/posts?per_page=1')
            ->assertOk()
            ->assertJsonStructure([
                '*' => ['id', 'slug', 'title' => ['rendered'], 'content' => ['rendered'], 'link'],
            ])
            ->assertHeader('X-WP-Total', '2')
            ->assertHeader('X-WP-TotalPages', '2');

        $this->assertCount(1, $response->json());
    }

    public function test_search_returns_matching_published_posts(): void
    {
        $this->makePost('find-me', 'published', 'post', ['title' => 'A Searchable Article']);
        $this->makePost('find-me-not', 'draft', 'post', ['title' => 'A Hidden Searchable Article']);

        $this->getJson('/api/search?q=Searchable')
            ->assertOk()
            ->assertJsonPath('meta.query', 'Searchable')
            ->assertJsonPath('data.0.slug', 'find-me');
    }

    protected function makePost(string $slug, string $status, string $type, array $attributes = []): Post
    {
        return Post::create(array_merge([
            'site_id' => $this->site->id,
            'user_id' => $this->user->id,
            'title' => str($slug)->replace('-', ' ')->title() . ' Post',
            'slug' => $slug,
            'content' => '<p>Body for ' . $slug . '.</p>',
            'post_type' => $type,
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null,
        ], $attributes));
    }
}
