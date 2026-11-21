<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiSiteScopingTest extends TestCase
{
    use RefreshDatabase;

    protected Site $alpha;
    protected Site $beta;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->alpha = Site::create([
            'name' => 'Alpha Site',
            'slug' => 'alpha-site',
            'domain' => 'alpha.test',
        ]);

        $this->beta = Site::create([
            'name' => 'Beta Site',
            'slug' => 'beta-site',
            'domain' => 'beta.test',
        ]);

        $this->user = User::factory()->create();
    }

    public function test_public_posts_are_scoped_to_the_requested_domain(): void
    {
        $alphaPost = $this->makePost($this->alpha, 'Alpha Article', 'shared-article');
        $betaPost = $this->makePost($this->beta, 'Beta Article', 'beta-article');

        $this->get('http://alpha.test/posts/shared-article')
            ->assertOk()
            ->assertSee('Alpha Article')
            ->assertDontSee('Beta Article');

        $this->get('http://alpha.test/posts/beta-article')->assertNotFound();
        $this->get('http://beta.test/posts/shared-article')->assertNotFound();

        $this->get('http://beta.test/blog')
            ->assertOk()
            ->assertSee('Beta Article')
            ->assertDontSee('Alpha Article');
    }

    public function test_category_and_tag_archives_are_scoped_to_the_domain(): void
    {
        $alphaCategory = Category::create([
            'site_id' => $this->alpha->id,
            'name' => 'News',
            'slug' => 'news',
        ]);

        $betaCategory = Category::create([
            'site_id' => $this->beta->id,
            'name' => 'News',
            'slug' => 'news',
        ]);

        $alphaPost = $this->makePost($this->alpha, 'Alpha News', 'alpha-news');
        $betaPost = $this->makePost($this->beta, 'Beta News', 'beta-news');

        $alphaPost->categories()->attach($alphaCategory);
        $betaPost->categories()->attach($betaCategory);

        $this->get('http://alpha.test/categories/news')
            ->assertOk()
            ->assertSee('Alpha News')
            ->assertDontSee('Beta News');

        $this->get('http://beta.test/categories/news')
            ->assertOk()
            ->assertSee('Beta News')
            ->assertDontSee('Alpha News');
    }

    public function test_the_api_is_scoped_to_the_requested_domain(): void
    {
        $this->makePost($this->alpha, 'Alpha API Article', 'alpha-api');
        $this->makePost($this->beta, 'Beta API Article', 'beta-api');

        $this->getJson('http://alpha.test/api/posts')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.title', 'Alpha API Article');

        $this->getJson('http://beta.test/wp-json/wp/v2/posts')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.title.rendered', 'Beta API Article');
    }

    protected function makePost(Site $site, string $title, string $slug): Post
    {
        return Post::create([
            'site_id' => $site->id,
            'user_id' => $this->user->id,
            'title' => $title,
            'slug' => $slug,
            'content' => '<p>Body for ' . $title . '.</p>',
            'post_type' => 'post',
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}
