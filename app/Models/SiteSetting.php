<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Language;
use App\Models\LanguageContent;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'analytics_code',
        'custom_css',
        'custom_js',
        'maintenance_mode',
        'disable_comments',
        'enable_comment_approval',
        'post_per_page',
        'tax_rate',
        'theme',
        'site_title',
        'site_description',
        'admin_email',
        // Spam protection
        'enable_akismet',
        'akismet_api_key',
        'spam_keywords',
        'rate_limit_enabled',
        'rate_limit_per_minute',
        'captcha_enabled',
        // Layout options
        'logo_url',
        'logo_text',
        'favicon_url',
        'primary_color',
        'secondary_color',
        'accent_color',
        'text_color',
        'bg_color',
        'border_color',
        'font_family',
        'container_width',
        // Typography tokens
        'body_font_size',
        'line_height',
        // Spacing tokens
        'block_gap',
        'section_padding',
        // Permalinks
        'permalink_structure',
        'custom_permalink',
        // SEO
        'seo_title_suffix',
        'seo_default_keywords',
        'enable_xml_sitemap',
        // Security
        'enable_debug_mode',
        'disable_file_editor',
        'max_upload_size',
        'enable_user_registration',
    ];

    protected $casts = [
        'maintenance_mode' => 'boolean',
        'disable_comments' => 'boolean',
        'enable_comment_approval' => 'boolean',
        'enable_akismet' => 'boolean',
        'rate_limit_enabled' => 'boolean',
        'captcha_enabled' => 'boolean',
        'enable_xml_sitemap' => 'boolean',
        'enable_debug_mode' => 'boolean',
        'disable_file_editor' => 'boolean',
        'enable_user_registration' => 'boolean',
        'post_per_page' => 'integer',
        'tax_rate' => 'decimal:2',
        'rate_limit_per_minute' => 'integer',
        'max_upload_size' => 'integer',
    ];

    /**
     * Get the site that owns the settings.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * The translated site title for the active language, falling back to the
     * stored (default-language) site title.
     */
    public function getResolvedSiteTitleAttribute(): ?string
    {
        return $this->translatedSetting('title') ?: $this->site_title;
    }

    /**
     * The translated site description for the active language, falling back to
     * the stored (default-language) description.
     */
    public function getResolvedSiteDescriptionAttribute(): ?string
    {
        return $this->translatedSetting('description') ?: $this->site_description;
    }

    /**
     * Look up a per-language translation for this site's settings.
     *
     * The translation is stored in the language_content table with
     * content_type='site_setting' and content_id=site id; the `meta` column
     * carries the per-field translations (title / description).
     */
    protected function translatedSetting(string $field): ?string
    {
        $language = request()->attributes->get('activeLanguage');

        if (!$language || !$this->site_id) {
            return null;
        }

        $default = Language::defaultLanguageFor($this->site);

        if (!$default || $language->code === $default->code) {
            return null;
        }

        $translation = LanguageContent::where('content_type', 'site_setting')
            ->where('content_id', $this->site_id)
            ->where('language_id', $language->id)
            ->first();

        $meta = $translation?->meta;

        if (!is_array($meta)) {
            return null;
        }

        $value = $meta[$field] ?? null;

        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    /**
     * Get the settings record for a site (or the default site), creating an
     * unsaved instance when none exists yet.
     */
    public static function forSite(?Site $site = null): self
    {
        $site = $site ?: Site::first();

        if (!$site) {
            $setting = new static();
            // Set default values when no site exists
            $setting->primary_color = '#3b82f6';
            $setting->secondary_color = '#6b7280';
            $setting->accent_color = '#10b981';
            $setting->text_color = '#1f2937';
            $setting->bg_color = '#ffffff';
            $setting->border_color = '#e5e7eb';
            $setting->site_title = 'Rosetta CMS';
            return $setting;
        }

        $setting = static::firstOrNew(['site_id' => $site->id]);

        // Set default values if the record is new (no id yet).
        // Deliberately leave site_title unset: callers fall back to the site's
        // own name so the CMS never misrepresents a site's identity.
        if (!$setting->exists) {
            $setting->primary_color = '#3b82f6';
            $setting->secondary_color = '#6b7280';
            $setting->accent_color = '#10b981';
            $setting->text_color = '#1f2937';
            $setting->bg_color = '#ffffff';
            $setting->border_color = '#e5e7eb';
            $setting->body_font_size = '16px';
            $setting->line_height = '1.6';
            $setting->block_gap = '2rem';
            $setting->section_padding = '3rem';
        }

        return $setting;
    }
}
