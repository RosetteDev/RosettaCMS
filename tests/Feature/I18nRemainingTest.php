<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\LanguageContent;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Post;
use App\Models\Role;
use App\Models\Site;
use App\Models\SiteSetting;
use App\Models\TranslationString;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class I18nRemainingTest extends TestCase
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
            'name' => 'I18n Site',
            'slug' => 'i18n-site',
            'domain' => 'i18n.test',
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

    // --- Per-site language default -----------------------------------------

    public function test_site_resolved_default_falls_back_to_global_default(): void
    {
        $default = $this->defaultLanguage();

        $this->assertSame('en', $this->site->resolvedDefaultLanguage()->code);
    }

    public function test_site_resolved_default_uses_site_specific_language(): void
    {
        $this->defaultLanguage();
        $french = $this->frenchLanguage();

        $this->site->update(['default_language_id' => $french->id]);

        $this->assertSame('fr', $this->site->fresh()->resolvedDefaultLanguage()->code);
    }

    public function test_language_default_language_for_site_respects_site_default(): void
    {
        $this->defaultLanguage();
        $french = $this->frenchLanguage();

        $this->site->update(['default_language_id' => $french->id]);

        $this->assertSame('fr', Language::defaultLanguageFor($this->site)->code);
    }

    public function test_site_update_persists_default_language(): void
    {
        $french = $this->frenchLanguage();

        $this->actingAs($this->user)->put(route('admin.sites.update', $this->site), [
            'name' => 'I18n Site',
            'domain' => 'i18n.test',
            'description' => '',
            'default_language_id' => $french->id,
        ])->assertRedirect();

        $this->assertSame($french->id, $this->site->fresh()->default_language_id);
    }

    // --- Translated slugs in public URLs ------------------------------------

    public function test_post_resolves_via_translated_slug(): void
    {
        $this->defaultLanguage();
        $french = $this->frenchLanguage();

        $post = Post::create([
            'site_id' => $this->site->id,
            'user_id' => $this->user->id,
            'title' => 'Hello World',
            'slug' => 'hello-world',
            'content' => '<p>Body.</p>',
            'post_type' => 'post',
            'status' => 'published',
            'published_at' => now(),
        ]);

        LanguageContent::create([
            'content_type' => 'post',
            'content_id' => $post->id,
            'language_id' => $french->id,
            'title' => 'Bonjour le monde',
            'content' => '<p>Corps.</p>',
            'slug' => 'bonjour-le-monde',
            'translated_by' => $this->user->id,
        ]);

        $response = $this->withSession(['active_language' => 'fr'])
            ->get('/posts/bonjour-le-monde');

        $response->assertOk();
        $response->assertSee('Bonjour le monde', false);
    }

    public function test_default_slug_still_resolves_for_translated_post(): void
    {
        $this->defaultLanguage();
        $french = $this->frenchLanguage();

        $post = Post::create([
            'site_id' => $this->site->id,
            'user_id' => $this->user->id,
            'title' => 'Hello World',
            'slug' => 'hello-world',
            'content' => '<p>Body.</p>',
            'post_type' => 'post',
            'status' => 'published',
            'published_at' => now(),
        ]);

        LanguageContent::create([
            'content_type' => 'post',
            'content_id' => $post->id,
            'language_id' => $french->id,
            'title' => 'Bonjour le monde',
            'slug' => 'bonjour-le-monde',
            'translated_by' => $this->user->id,
        ]);

        $this->withSession(['active_language' => 'fr'])
            ->get('/posts/hello-world')
            ->assertOk()
            ->assertSee('Bonjour le monde', false);
    }

    public function test_page_resolves_via_translated_slug(): void
    {
        $this->defaultLanguage();
        $french = $this->frenchLanguage();

        $page = Post::create([
            'site_id' => $this->site->id,
            'user_id' => $this->user->id,
            'title' => 'About Us',
            'slug' => 'about-us',
            'content' => '<p>About body.</p>',
            'post_type' => 'page',
            'status' => 'published',
            'published_at' => now(),
        ]);

        LanguageContent::create([
            'content_type' => 'page',
            'content_id' => $page->id,
            'language_id' => $french->id,
            'title' => 'À propos',
            'slug' => 'a-propos',
            'translated_by' => $this->user->id,
        ]);

        $this->withSession(['active_language' => 'fr'])
            ->get('/pages/a-propos')
            ->assertOk()
            ->assertSee('À propos', false);
    }

    public function test_menu_item_resolved_url_uses_translated_slug(): void
    {
        $this->defaultLanguage();
        $french = $this->frenchLanguage();

        $page = Post::create([
            'site_id' => $this->site->id,
            'user_id' => $this->user->id,
            'title' => 'About Us',
            'slug' => 'about-us',
            'content' => '<p>About body.</p>',
            'post_type' => 'page',
            'status' => 'published',
            'published_at' => now(),
        ]);

        LanguageContent::create([
            'content_type' => 'page',
            'content_id' => $page->id,
            'language_id' => $french->id,
            'title' => 'À propos',
            'slug' => 'a-propos',
            'translated_by' => $this->user->id,
        ]);

        $menu = Menu::create([
            'site_id' => $this->site->id,
            'name' => 'Header Menu',
            'slug' => 'header-menu',
            'location' => 'header',
        ]);

        $item = MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'About',
            'type' => 'page',
            'page_id' => $page->id,
            'order' => 0,
        ]);

        request()->attributes->set('activeLanguage', $french);

        $this->assertStringContainsString('/pages/a-propos', $item->fresh()->resolved_url);
    }

    // --- Translated navigation menus ----------------------------------------

    public function test_menu_item_translation_is_persisted_and_rendered(): void
    {
        $this->defaultLanguage();
        $french = $this->frenchLanguage();

        $menu = Menu::create([
            'site_id' => $this->site->id,
            'name' => 'Header Menu',
            'slug' => 'header-menu',
            'location' => 'header',
        ]);

        $this->actingAs($this->user)->post(route('admin.menus.items.store', $menu), [
            'title' => 'Contact',
            'type' => 'custom',
            'url' => '/contact',
            'translations' => [
                $french->id => ['title' => 'Contact Français'],
            ],
        ])->assertRedirect();

        $item = $menu->items()->first();
        $this->assertNotNull($item);

        $translation = LanguageContent::where('content_type', 'menu_item')
            ->where('content_id', $item->id)
            ->where('language_id', $french->id)
            ->first();

        $this->assertNotNull($translation);
        $this->assertSame('Contact Français', $translation->title);

        request()->attributes->set('activeLanguage', $french);

        $this->assertSame('Contact Français', $item->fresh()->resolved_title);
    }

    public function test_menu_item_translation_can_be_removed_by_empty_value(): void
    {
        $this->defaultLanguage();
        $french = $this->frenchLanguage();

        $menu = Menu::create([
            'site_id' => $this->site->id,
            'name' => 'Header Menu',
            'slug' => 'header-menu',
            'location' => 'header',
        ]);

        $item = MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Contact',
            'type' => 'custom',
            'url' => '/contact',
            'order' => 0,
        ]);

        LanguageContent::create([
            'content_type' => 'menu_item',
            'content_id' => $item->id,
            'language_id' => $french->id,
            'title' => 'Contact Français',
            'translated_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)->put(route('admin.menus.items.update', [$menu, $item]), [
            'title' => 'Contact',
            'type' => 'custom',
            'url' => '/contact',
            'translations' => [
                $french->id => ['title' => ''],
            ],
        ])->assertRedirect();

        $this->assertNull(LanguageContent::where('content_type', 'menu_item')
            ->where('content_id', $item->id)
            ->where('language_id', $french->id)
            ->first());
    }

    public function test_homepage_nav_renders_translated_menu_title(): void
    {
        $this->defaultLanguage();
        $french = $this->frenchLanguage();

        $menu = Menu::create([
            'site_id' => $this->site->id,
            'name' => 'Header Menu',
            'slug' => 'header-menu',
            'location' => 'header',
        ]);

        $item = MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Contact',
            'type' => 'custom',
            'url' => '/contact',
            'order' => 0,
        ]);

        LanguageContent::create([
            'content_type' => 'menu_item',
            'content_id' => $item->id,
            'language_id' => $french->id,
            'title' => 'Contactez-nous',
            'translated_by' => $this->user->id,
        ]);

        $this->withSession(['active_language' => 'fr'])
            ->get('/')
            ->assertOk()
            ->assertSee('Contactez-nous', false);
    }

    // --- Translated site settings -------------------------------------------

    public function test_settings_translation_is_persisted_and_rendered(): void
    {
        $this->defaultLanguage();
        $french = $this->frenchLanguage();

        SiteSetting::create([
            'site_id' => $this->site->id,
            'site_title' => 'Test Site',
            'site_description' => 'A test site.',
            'admin_email' => 'admin@i18n.test',
        ]);

        $this->actingAs($this->user)->post('/admin/settings/general', [
            'site_name' => 'Test Site',
            'site_description' => 'A test site.',
            'admin_email' => 'admin@i18n.test',
            'translations' => [
                $french->id => [
                    'title' => 'Site de test',
                    'description' => 'Un site de test.',
                ],
            ],
        ])->assertRedirect();

        $translation = LanguageContent::where('content_type', 'site_setting')
            ->where('content_id', $this->site->id)
            ->where('language_id', $french->id)
            ->first();

        $this->assertNotNull($translation);
        $this->assertSame('Site de test', $translation->title);
        $this->assertSame('Un site de test.', $translation->meta['description']);

        $this->withSession(['active_language' => 'fr'])
            ->get('/')
            ->assertOk()
            ->assertSee('Site de test', false);
    }

    // --- Translation memory ---------------------------------------------------

    public function test_admin_can_store_and_delete_translation_strings(): void
    {
        $french = $this->frenchLanguage();

        $this->actingAs($this->user)->post(route('admin.translations.store'), [
            'key' => 'nav.contact',
            'language_id' => $french->id,
            'value' => 'Contactez-nous',
        ])->assertRedirect(route('admin.translations.index'));

        $string = TranslationString::where('key', 'nav.contact')
            ->where('language_id', $french->id)
            ->first();

        $this->assertNotNull($string);
        $this->assertSame('Contactez-nous', $string->value);

        $this->actingAs($this->user)
            ->delete(route('admin.translations.destroy', $string))
            ->assertRedirect(route('admin.translations.index'));

        $this->assertNull(TranslationString::where('key', 'nav.contact')
            ->where('language_id', $french->id)
            ->first());
    }

    public function test_admin_translation_index_renders_for_admin(): void
    {
        $this->defaultLanguage();
        $this->frenchLanguage();

        $this->actingAs($this->user)
            ->get(route('admin.translations.index'))
            ->assertOk()
            ->assertSee('Translations');
    }

    public function test_reusable_string_helper_returns_translation_and_fallback(): void
    {
        $this->defaultLanguage();
        $french = $this->frenchLanguage();

        TranslationString::create([
            'key' => 'nav.contact',
            'language_id' => $french->id,
            'value' => 'Contactez-nous',
        ]);

        request()->attributes->set('activeLanguage', $french);

        $this->assertSame('Contactez-nous', reusable_string('nav.contact'));
        $this->assertSame('Fallback', reusable_string('missing.key', 'Fallback'));
        $this->assertNull(reusable_string('missing.key'));
    }
}
