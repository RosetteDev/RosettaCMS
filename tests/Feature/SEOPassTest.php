<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\LanguageContent;
use App\Models\Media;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\Role;
use App\Models\Site;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SEOPassTest extends TestCase
{
    use RefreshDatabase;

    protected Site $site;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = Site::create([
            'name' => 'SEO Test Site',
            'slug' => 'seo-test-site',
            'domain' => 'seo.test',
        ]);

        $this->user = User::factory()->create([
            'email' => 'seo-admin@test.dev',
            'password' => bcrypt('password'),
        ]);

        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Test admin',
            'permissions' => [],
        ]);
        $this->user->roles()->attach($adminRole);

        SiteSetting::create(['site_id' => $this->site->id]);
    }

    protected function makePost(array $overrides = []): Post
    {
        return Post::create(array_merge([
            'site_id' => $this->site->id,
            'user_id' => $this->user->id,
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => '<p>Hello world.</p>',
            'post_type' => 'post',
            'status' => 'published',
            'published_at' => now(),
        ], $overrides));
    }

    protected function admin(): User
    {
        return $this->user;
    }

    // --- Title template & head tags -----------------------------------

    public function test_title_template_is_applied_to_pages(): void
    {
        SiteSetting::where('site_id', $this->site->id)->first()->update([
            'seo_title_template' => '%title% — %site%',
        ]);

        $response = $this->get('/blog');

        $response->assertOk();
        $response->assertSee('<title>Blog — SEO Test Site</title>', false);
    }

    public function test_default_description_falls_back_and_og_image_from_logo(): void
    {
        SiteSetting::where('site_id', $this->site->id)->first()->update([
            'site_description' => 'A great site',
            'logo_url' => 'https://cdn.example.com/logo.png',
        ]);

        $response = $this->get('/blog');

        $response->assertOk();
        $response->assertSee('<meta name="description" content="A great site">', false);
        $response->assertSee('<meta property="og:image" content="https://cdn.example.com/logo.png">', false);
    }

    public function test_twitter_handle_and_verification_meta_are_emitted(): void
    {
        SiteSetting::where('site_id', $this->site->id)->first()->update([
            'twitter_handle' => '@seotest',
            'verification_meta' => ['google' => 'abc123', 'bing' => 'xyz789'],
        ]);

        $response = $this->get('/blog');

        $response->assertOk();
        $response->assertSee('<meta name="twitter:site" content="@seotest">', false);
        $response->assertSee('<meta name="google-site-verification" content="abc123">', false);
        $response->assertSee('<meta name="msvalidate.01" content="xyz789">', false);
    }

    public function test_og_locale_reflects_active_language(): void
    {
        request()->attributes->set('activeLanguage', Language::defaultLanguage());

        $response = $this->get('/blog');

        $response->assertOk();
        $response->assertSee('property="og:locale"', false);
    }

    // --- JSON-LD structured data ---------------------------------------

    public function test_post_page_emits_article_and_breadcrumb_json_ld(): void
    {
        $post = $this->makePost([
            'title' => 'Structured Data Post',
            'slug' => 'structured-data-post',
        ]);

        $response = $this->get('/posts/' . $post->slug);

        $response->assertOk();
        $response->assertSee('application/ld+json', false);

        $contents = $response->getContent();
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $contents, $matches);

        $schemas = array_map(fn ($json) => json_decode($json, true), $matches[1]);
        $types = array_column($schemas, '@type');

        $this->assertContains('Article', $types);
        $this->assertContains('BreadcrumbList', $types);

        $article = collect($schemas)->firstWhere('@type', 'Article');
        $this->assertSame('Structured Data Post', $article['headline']);
        $this->assertSame($post->public_url, $article['url']);

        $breadcrumb = collect($schemas)->firstWhere('@type', 'BreadcrumbList');
        $this->assertSame('Blog', $breadcrumb['itemListElement'][1]['name']);
        $this->assertSame('Structured Data Post', $breadcrumb['itemListElement'][2]['name']);
    }

    public function test_post_with_faq_meta_emits_faq_page_schema(): void
    {
        $post = $this->makePost([
            'title' => 'FAQ Post',
            'slug' => 'faq-post',
            'meta' => ['faq' => [['question' => 'What is it?', 'answer' => 'A thing.']]],
        ]);

        $response = $this->get('/posts/' . $post->slug);

        $response->assertOk();

        $contents = $response->getContent();
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $contents, $matches);

        $schemas = array_map(fn ($json) => json_decode($json, true), $matches[1]);
        $types = array_column($schemas, '@type');

        $this->assertContains('FAQPage', $types);

        $faq = collect($schemas)->firstWhere('@type', 'FAQPage');
        $this->assertSame('What is it?', $faq['mainEntity'][0]['name']);
        $this->assertSame('A thing.', $faq['mainEntity'][0]['acceptedAnswer']['text']);
    }

    public function test_homepage_emits_website_schema(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        $contents = $response->getContent();
        $this->assertStringContainsString('"@type":"Website"', $contents);
    }

    // --- Sitemap --------------------------------------------------------

    public function test_sitemap_index_lists_split_sitemaps(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee('<sitemap>', false);
        $response->assertSee('/sitemap-posts.xml', false);
        $response->assertSee('/sitemap-pages.xml', false);
        $response->assertSee('/sitemap-categories.xml', false);
        $response->assertSee('/sitemap-tags.xml', false);
    }

    public function test_posts_sitemap_excludes_drafts_and_noindex_posts(): void
    {
        $published = $this->makePost(['slug' => 'published-post']);
        $draft = $this->makePost(['slug' => 'draft-post', 'status' => 'draft', 'published_at' => null]);
        $noindex = $this->makePost([
            'slug' => 'noindex-post',
            'meta' => ['robots' => 'noindex'],
        ]);

        $response = $this->get('/sitemap-posts.xml');

        $response->assertOk();
        $response->assertSee('/posts/published-post', false);
        $response->assertDontSee('/posts/draft-post', false);
        $response->assertDontSee('/posts/noindex-post', false);
    }

    public function test_posts_sitemap_includes_image_entries_and_alternates(): void
    {
        $post = $this->makePost([
            'slug' => 'image-post',
            'featured_image' => 'media/test.png',
        ]);

        // Add a default language and a translated variant so hreflang alternates are generated.
        Language::create([
            'site_id' => $this->site->id,
            'name' => 'English',
            'code' => 'en',
            'is_active' => true,
            'is_default' => true,
            'sort_order' => 0,
        ]);

        $other = Language::create([
            'site_id' => $this->site->id,
            'name' => 'French',
            'code' => 'fr',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 2,
        ]);

        LanguageContent::create([
            'content_type' => 'post',
            'content_id' => $post->id,
            'language_id' => $other->id,
            'slug' => 'image-post-fr',
            'title' => 'Image Post (FR)',
        ]);

        $response = $this->get('/sitemap-posts.xml');

        $response->assertOk();
        $response->assertSee('<image:loc>', false);
        $response->assertSee('/posts/image-post-fr', false);
        $response->assertSee('hreflang="fr"', false);
    }

    public function test_sitemap_respects_enabled_setting(): void
    {
        SiteSetting::where('site_id', $this->site->id)->first()->update([
            'enable_xml_sitemap' => false,
        ]);

        $this->get('/sitemap.xml')->assertNotFound();
        $this->get('/sitemap-posts.xml')->assertNotFound();
    }

    // --- robots.txt ------------------------------------------------------

    public function test_robots_txt_contains_default_rules_and_sitemap(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertSee('User-agent: *');
        $response->assertSee('Disallow: /admin');
        $response->assertSee('Sitemap: ');
    }

    public function test_robots_txt_includes_per_site_allow_and_disallow_rules(): void
    {
        SiteSetting::where('site_id', $this->site->id)->first()->update([
            'robots_allow' => "/blog\n/pages/about",
            'robots_disallow' => "/private\n/search-results",
        ]);

        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertSee('Allow: /blog');
        $response->assertSee('Allow: /pages/about');
        $response->assertSee('Disallow: /private');
        $response->assertSee('Disallow: /search-results');
    }

    // --- Redirects --------------------------------------------------------

    public function test_active_redirect_is_applied_and_hit_count_increments(): void
    {
        $redirect = Redirect::create([
            'site_id' => $this->site->id,
            'from_path' => 'old-page',
            'to_url' => '/new-page',
            'type' => '301',
            'is_active' => true,
        ]);

        $response = $this->get('/old-page');

        $response->assertRedirect('/new-page');
        $this->assertSame(301, $response->status());

        $this->assertSame(1, $redirect->refresh()->hit_count);
    }

    public function test_302_redirect_uses_302_status(): void
    {
        Redirect::create([
            'site_id' => $this->site->id,
            'from_path' => 'temp-move',
            'to_url' => 'https://example.com/target',
            'type' => '302',
            'is_active' => true,
        ]);

        $response = $this->get('/temp-move');

        $response->assertRedirect('https://example.com/target');
        $this->assertSame(302, $response->status());
    }

    public function test_inactive_redirect_is_ignored(): void
    {
        Redirect::create([
            'site_id' => $this->site->id,
            'from_path' => 'old-page',
            'to_url' => '/new-page',
            'type' => '301',
            'is_active' => false,
        ]);

        $this->get('/old-page')->assertNotFound();
    }

    public function test_redirect_is_scoped_to_its_site(): void
    {
        $otherSite = Site::create([
            'name' => 'Other Site',
            'slug' => 'other-site',
            'domain' => 'other.test',
        ]);

        // The request resolves the FIRST site (default), not the other site.
        Redirect::create([
            'site_id' => $otherSite->id,
            'from_path' => 'old-page',
            'to_url' => '/new-page',
            'type' => '301',
            'is_active' => true,
        ]);

        $this->get('/old-page')->assertNotFound();
    }

    public function test_admin_redirects_page_renders_and_store_toggle_destroy_work(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/redirects');
        $response->assertOk();
        $response->assertSee('Redirects');

        $response = $this->actingAs($this->user)->post('/admin/redirects', [
            'from_path' => '/old-page',
            'to_url' => '/new-page',
            'type' => '301',
        ]);

        $response->assertRedirect(route('admin.redirects.index'));
        $this->assertDatabaseHas('redirects', [
            'site_id' => $this->site->id,
            'from_path' => 'old-page',
            'to_url' => '/new-page',
            'type' => '301',
        ]);

        $redirect = Redirect::where('from_path', 'old-page')->first();

        $response = $this->actingAs($this->user)->post("/admin/redirects/{$redirect->id}/toggle");
        $response->assertRedirect(route('admin.redirects.index'));
        $this->assertFalse($redirect->refresh()->is_active);

        $response = $this->actingAs($this->user)->delete("/admin/redirects/{$redirect->id}");
        $response->assertRedirect(route('admin.redirects.index'));
        $this->assertDatabaseMissing('redirects', ['id' => $redirect->id]);
    }

    public function test_admin_redirects_page_requires_admin_role(): void
    {
        $editor = User::factory()->create(['email' => 'editor@test.dev']);
        $editorRole = Role::create(['name' => 'Editor', 'slug' => 'editor', 'permissions' => []]);
        $editor->roles()->attach($editorRole);

        $this->actingAs($editor)->get('/admin/redirects')->assertForbidden();
    }

    // --- SEO health page --------------------------------------------------

    public function test_seo_health_page_renders_checks(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/seo-health');

        $response->assertOk();
        $response->assertSee('SEO Health');
        $response->assertSee('XML sitemap enabled');
        $response->assertSee('Favicon configured');
        $response->assertSee('Default OG image');
    }

    public function test_seo_health_fix_buttons_apply_one_click_fixes(): void
    {
        SiteSetting::where('site_id', $this->site->id)->first()->update([
            'logo_url' => 'https://cdn.example.com/logo.png',
            'enable_xml_sitemap' => false,
        ]);

        // Favicon from logo
        $response = $this->actingAs($this->user)->post('/admin/seo-health/fix/favicon-from-logo');
        $response->assertRedirect();
        $this->assertSame('https://cdn.example.com/logo.png', SiteSetting::where('site_id', $this->site->id)->first()->favicon_url);

        // OG image from logo
        $response = $this->actingAs($this->user)->post('/admin/seo-health/fix/og-from-logo');
        $response->assertRedirect();
        $this->assertSame('https://cdn.example.com/logo.png', SiteSetting::where('site_id', $this->site->id)->first()->og_image);

        // Enable sitemap
        $response = $this->actingAs($this->user)->post('/admin/seo-health/fix/enable-sitemap');
        $response->assertRedirect();
        $this->assertTrue((bool) SiteSetting::where('site_id', $this->site->id)->first()->enable_xml_sitemap);
    }

    public function test_seo_health_reports_missing_alt_text(): void
    {
        Media::create([
            'site_id' => $this->site->id,
            'user_id' => $this->user->id,
            'file_name' => 'no-alt.png',
            'file_path' => 'media/no-alt.png',
            'mime_type' => 'image/png',
            'file_size' => 100,
            'alt_text' => null,
        ]);

        $response = $this->actingAs($this->user)->get('/admin/seo-health');

        $response->assertOk();
        $response->assertSee('1 image(s) missing alt text');
    }

    public function test_seo_health_reports_broken_links(): void
    {
        $post = $this->makePost([
            'slug' => 'link-post',
            'content' => '<p><a href="/posts/definitely-missing">broken</a></p>',
        ]);

        $response = $this->actingAs($this->user)->get('/admin/seo-health');

        $response->assertOk();
        $response->assertSee('/posts/definitely-missing');
    }

    // --- Media alt enforcement ---------------------------------------------

    public function test_image_upload_requires_alt_text(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('photo.png', 100, 100);

        $response = $this->actingAs($this->user)->post('/admin/media', [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('media', 0);
    }

    public function test_image_upload_with_alt_text_succeeds(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('photo.png', 100, 100);

        $response = $this->actingAs($this->user)->post('/admin/media', [
            'file' => $file,
            'alt_text' => 'A test photo',
        ]);

        $response->assertRedirect(route('admin.media.index'));
        $this->assertDatabaseCount('media', 1);

        $media = Media::first();
        $this->assertSame('A test photo', $media->alt_text);
    }

    // --- 410 error page ------------------------------------------------------

    public function test_410_error_page_renders_with_noindex(): void
    {
        $html = view('errors.410', ['status' => 410])->render();

        $this->assertStringContainsString('noindex', $html);
        $this->assertStringContainsString('410', $html);
    }

    // --- Anonymous 404/410 pages reveal no site internals (audit #28) -----

    public function test_anonymous_404_page_is_generic_and_leaks_no_site_details(): void
    {
        $site = Site::create(['name' => 'Secret Internal Site', 'slug' => 'secret-internal-site', 'domain' => 'secret.test']);
        SiteSetting::forSite($site);

        $response = $this->get('/definitely-does-not-exist-' . uniqid());

        $response->assertStatus(404);
        $response->assertSee('Page not found');
        $response->assertSee('noindex', false);
        // No theme identity, site name, menus, or settings.
        $response->assertDontSee('Secret Internal Site');
        $response->assertDontSee('themes/');
        $response->assertDontSee('stylesheet');
    }

    public function test_anonymous_410_page_is_generic(): void
    {
        $html = view('errors.410', ['status' => 410])->render();

        $this->assertStringContainsString('410', $html);
        $this->assertStringContainsString('noindex', $html);
        $this->assertStringNotContainsString('themes/', $html);
        $this->assertStringNotContainsString('stylesheet', $html);
    }

    public function test_authenticated_404_page_keeps_the_branded_layout(): void
    {
        $site = Site::create(['name' => 'Branded Test Site', 'slug' => 'branded-test-site', 'domain' => 'branded.test']);
        SiteSetting::forSite($site);

        $response = $this->actingAs($this->user)->get('/definitely-does-not-exist-' . uniqid());

        $response->assertStatus(404);
        // Branded page renders the theme stylesheet link, not the generic one.
        $response->assertSee('themes/', false);
    }

    public function test_settings_page_renders_og_image_picker(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/settings/seo');

        $response->assertOk();
        $response->assertSee('ogImagePickerModal');
        $response->assertSee('og-image-picker-grid');
    }

    // --- Settings update ------------------------------------------------------

    public function test_settings_update_persists_new_seo_fields(): void
    {
        $response = $this->actingAs($this->user)->post('/admin/settings/seo', [
            'site_name' => 'SEO Test Site',
            'admin_email' => 'seo-admin@test.dev',
            'posts_per_page' => 10,
            'seo_title_template' => '%title% | %site%',
            'seo_default_keywords' => 'seo, cms',
            'og_image' => 'https://cdn.example.com/og.png',
            'twitter_handle' => '@seotest',
            'verification_google' => 'google-verify-123',
            'verification_bing' => 'bing-verify-456',
            'robots_allow' => "/blog\n/about",
            'robots_disallow' => "/private",
            'enable_xml_sitemap' => 1,
        ]);

        $response->assertRedirect();

        $settings = SiteSetting::where('site_id', $this->site->id)->first();
        $this->assertSame('%title% | %site%', $settings->seo_title_template);
        $this->assertSame('https://cdn.example.com/og.png', $settings->og_image);
        $this->assertSame('@seotest', $settings->twitter_handle);
        $this->assertSame(['google' => 'google-verify-123', 'bing' => 'bing-verify-456'], $settings->verification_meta);
        $this->assertSame("/blog\n/about", $settings->robots_allow);
        $this->assertSame("/private", $settings->robots_disallow);
        $this->assertTrue((bool) $settings->enable_xml_sitemap);
    }
}
