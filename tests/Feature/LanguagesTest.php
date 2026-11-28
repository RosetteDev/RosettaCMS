<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\LanguageContent;
use App\Models\Post;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin', 'permissions' => []]);
        $this->user->roles()->attach($role);

        $this->site = Site::create([
            'name' => 'Language Test Site',
            'slug' => 'language-test-site',
            'domain' => 'language.test',
        ]);
    }

    protected function defaultLanguage(): Language
    {
        return Language::create([
            'code' => 'en',
            'name' => 'English',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    protected function frenchLanguage(): Language
    {
        return Language::create([
            'code' => 'fr',
            'name' => 'French',
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_admin_can_create_a_language(): void
    {
        $this->actingAs($this->user);

        $this->post(route('admin.languages.store'), [
            'code' => 'fr',
            'name' => 'French',
            'native_name' => 'Français',
            'flag' => '🇫🇷',
            'sort_order' => 1,
            'is_default' => '0',
            'is_active' => '1',
        ])->assertRedirect(route('admin.languages.index'));

        $language = Language::where('code', 'fr')->first();
        $this->assertNotNull($language);
        $this->assertSame('French', $language->name);
        $this->assertFalse($language->is_default);
    }

    public function test_creating_a_default_language_unsets_the_previous_default(): void
    {
        $default = $this->defaultLanguage();

        $this->actingAs($this->user);

        $this->post(route('admin.languages.store'), [
            'code' => 'fr',
            'name' => 'French',
            'is_default' => '1',
            'is_active' => '1',
        ])->assertRedirect(route('admin.languages.index'));

        $this->assertFalse($default->fresh()->is_default);
        $this->assertTrue(Language::where('code', 'fr')->first()->is_default);
    }

    public function test_default_language_cannot_be_deleted(): void
    {
        $default = $this->defaultLanguage();

        $this->actingAs($this->user);

        $this->delete(route('admin.languages.destroy', $default))
            ->assertRedirect(route('admin.languages.index'));

        $this->assertNotNull($default->fresh());
    }

    public function test_non_default_language_can_be_deleted(): void
    {
        $this->defaultLanguage();
        $french = $this->frenchLanguage();

        $this->actingAs($this->user);

        $this->delete(route('admin.languages.destroy', $french))
            ->assertRedirect(route('admin.languages.index'));

        $this->assertNull($french->fresh());
    }

    public function test_language_switcher_route_sets_session_and_redirects_back(): void
    {
        $this->defaultLanguage();
        $this->frenchLanguage();

        $this->get(route('language.switch', 'fr'), ['HTTP_REFERER' => url('/')])
            ->assertRedirect(url('/'));

        $this->assertSame('fr', session('active_language'));
    }

    public function test_language_switcher_rejects_unknown_codes(): void
    {
        $this->defaultLanguage();

        $this->get(route('language.switch', 'xx'), ['HTTP_REFERER' => url('/')])
            ->assertRedirect(url('/'));

        $this->assertNull(session('active_language'));
    }

    public function test_language_switcher_ignores_cross_origin_referer(): void
    {
        $this->defaultLanguage();
        $this->frenchLanguage();

        $response = $this->get(route('language.switch', 'fr'), ['HTTP_REFERER' => 'https://evil.example.com/phish']);

        $response->assertRedirect('/');
        $this->assertNull(session('active_language'));
    }

    public function test_language_switcher_redirects_to_home_without_referer(): void
    {
        $this->defaultLanguage();
        $this->frenchLanguage();

        $this->get(route('language.switch', 'fr'))
            ->assertRedirect('/');

        $this->assertNull(session('active_language'));
    }

    public function test_homepage_renders_with_active_language_locale(): void
    {
        $this->defaultLanguage();
        $this->frenchLanguage();

        $response = $this->withSession(['active_language' => 'fr'])->get('/');

        $response->assertOk();
        $this->assertSame('fr', app()->getLocale());
    }

    public function test_post_renders_translated_title_in_the_active_language(): void
    {
        $default = $this->defaultLanguage();
        $french = $this->frenchLanguage();

        $post = Post::create([
            'site_id' => $this->site->id,
            'user_id' => $this->user->id,
            'title' => 'Original Title',
            'slug' => 'original-title',
            'content' => '<p>Original body.</p>',
            'post_type' => 'post',
            'status' => 'published',
            'published_at' => now(),
        ]);

        LanguageContent::create([
            'content_type' => 'post',
            'content_id' => $post->id,
            'language_id' => $french->id,
            'title' => 'Titre traduit',
            'content' => '<p>Corps traduit.</p>',
            'excerpt' => null,
            'slug' => null,
            'translated_by' => $this->user->id,
        ]);

        $this->withSession(['active_language' => 'fr'])
            ->get('/posts/original-title')
            ->assertOk()
            ->assertSee('Titre traduit', false);
    }

    public function test_post_renders_untranslated_when_no_translation_exists(): void
    {
        $this->defaultLanguage();
        $this->frenchLanguage();

        $post = Post::create([
            'site_id' => $this->site->id,
            'user_id' => $this->user->id,
            'title' => 'Untranslated Post',
            'slug' => 'untranslated-post',
            'content' => '<p>Body.</p>',
            'post_type' => 'post',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->withSession(['active_language' => 'fr'])
            ->get('/posts/untranslated-post')
            ->assertOk()
            ->assertSee('Untranslated Post', false);
    }

    public function test_save_post_persists_translations(): void
    {
        $this->defaultLanguage();
        $french = $this->frenchLanguage();

        $this->actingAs($this->user);

        $this->post(route('admin.posts.store'), [
            'title' => 'Translatable Post',
            'slug' => 'translatable-post',
            'content' => '<p>Body.</p>',
            'status' => 'published',
            'translations' => [
                $french->id => [
                    'title' => 'Article traduisible',
                    'excerpt' => 'Extrait traduit',
                    'content' => '<p>Corps traduit.</p>',
                ],
            ],
        ]);

        $post = Post::where('slug', 'translatable-post')->first();
        $this->assertNotNull($post);

        $translation = LanguageContent::where('content_type', 'post')
            ->where('content_id', $post->id)
            ->where('language_id', $french->id)
            ->first();

        $this->assertNotNull($translation);
        $this->assertSame('Article traduisible', $translation->title);
        $this->assertSame('Extrait traduit', $translation->excerpt);
        $this->assertStringContainsString('Corps traduit', $translation->content);
    }

    public function test_languages_index_renders_for_admin(): void
    {
        $this->defaultLanguage();
        $this->frenchLanguage();

        $this->actingAs($this->user)
            ->get(route('admin.languages.index'))
            ->assertOk()
            ->assertSee('English')
            ->assertSee('French');
    }

    public function test_language_create_form_renders_for_admin(): void
    {
        $this->actingAs($this->user)
            ->get(route('admin.languages.create'))
            ->assertOk()
            ->assertSee('Add Language');
    }

    public function test_empty_translations_remove_stored_translation(): void
    {
        $this->defaultLanguage();
        $french = $this->frenchLanguage();

        $post = Post::create([
            'site_id' => $this->site->id,
            'user_id' => $this->user->id,
            'title' => 'Existing Translated Post',
            'slug' => 'existing-translated-post',
            'content' => '<p>Body.</p>',
            'post_type' => 'post',
            'status' => 'published',
            'published_at' => now(),
        ]);

        LanguageContent::create([
            'content_type' => 'post',
            'content_id' => $post->id,
            'language_id' => $french->id,
            'title' => 'Titre existant',
            'content' => '<p>Contenu existant.</p>',
            'excerpt' => null,
            'slug' => null,
            'translated_by' => $this->user->id,
        ]);

        $this->actingAs($this->user);

        $this->put(route('admin.posts.update', $post), [
            'title' => 'Existing Translated Post',
            'slug' => 'existing-translated-post',
            'content' => '<p>Body.</p>',
            'status' => 'published',
            'translations' => [
                $french->id => [
                    'title' => '',
                    'excerpt' => '',
                    'content' => '',
                ],
            ],
        ]);

        $this->assertNull(
            LanguageContent::where('content_type', 'post')
                ->where('content_id', $post->id)
                ->where('language_id', $french->id)
                ->first()
        );
    }
}
