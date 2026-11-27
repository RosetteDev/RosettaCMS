<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Language;
use App\Models\LanguageContent;
use App\Models\Post;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'type',
        'page_id',
        'url',
        'target',
        'order',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function page()
    {
        return $this->belongsTo(Post::class, 'page_id');
    }

    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    /**
     * Resolve the final link for this item, whether it points at a page or a
     * custom URL.
     *
     * When the active language differs from the site default and the linked
     * post/page has a translated slug, the link points at the translated slug.
     */
    public function getResolvedUrlAttribute(): string
    {
        if ($this->type === 'page' && $this->page) {
            return url('/pages/' . $this->localizedSlug($this->page));
        }

        if ($this->type === 'post' && $this->page) {
            return url('/posts/' . $this->localizedSlug($this->page));
        }

        if (!empty($this->url)) {
            return $this->url;
        }

        return '#';
    }

    /**
     * The translated title for the active language, falling back to the
     * stored (default-language) title.
     */
    public function getResolvedTitleAttribute(): string
    {
        $language = request()->attributes->get('activeLanguage');

        if (!$language) {
            return $this->title;
        }

        $default = $this->siteDefaultLanguage();

        if (!$default || $language->code === $default->code) {
            return $this->title;
        }

        $translation = LanguageContent::where('content_type', 'menu_item')
            ->where('content_id', $this->id)
            ->where('language_id', $language->id)
            ->first();

        return $translation?->title ?: $this->title;
    }

    /**
     * The linked post's slug in the active language.
     */
    protected function localizedSlug(Post $post): string
    {
        $language = request()->attributes->get('activeLanguage');

        if (!$language) {
            return $post->slug;
        }

        $default = $this->siteDefaultLanguage();

        if (!$default || $language->code === $default->code) {
            return $post->slug;
        }

        $translation = LanguageContent::where('content_type', $post->post_type)
            ->where('content_id', $post->id)
            ->where('language_id', $language->id)
            ->first();

        return $translation?->slug ?: $post->slug;
    }

    /**
     * The default language for the site that owns this menu item.
     */
    protected function siteDefaultLanguage(): ?Language
    {
        $site = $this->menu?->site;

        return Language::defaultLanguageFor($site);
    }
}
