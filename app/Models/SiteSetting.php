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
        'custom_form_fields',
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
        'seo_title_template',
        'seo_default_keywords',
        'og_image',
        'twitter_handle',
        'verification_meta',
        'robots_disallow',
        'robots_allow',
        'enable_xml_sitemap',
        // Security
        'enable_debug_mode',
        'disable_file_editor',
        'max_upload_size',
        'enable_user_registration',
        'enable_social_login_google',
        'enable_social_login_github',
        'enable_welcome_email',
        'enable_booking_form',
        'enable_chat_widget',
    ];

    protected $casts = [
        'verification_meta' => 'array',
        'custom_form_fields' => 'array',
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
        'enable_social_login_google' => 'boolean',
        'enable_social_login_github' => 'boolean',
        'enable_welcome_email' => 'boolean',
        'enable_booking_form' => 'boolean',
        'enable_chat_widget' => 'boolean',
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

    /**
     * Normalized list of verification meta tags to emit in the head.
     * Returns [engine => content] for engines that have a value.
     */
    public function verificationMetas(): array
    {
        $meta = $this->verification_meta;

        if (!is_array($meta)) {
            return [];
        }

        return collect($meta)
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->map(fn ($value) => trim($value))
            ->toArray();
    }

    /**
     * Lines from a robots text setting, one per line, trimmed and filtered.
     */
    public function robotsLines(string $field): array
    {
        $value = $this->{$field} ?? null;

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        return collect(preg_split('/\r?\n/', $value))
            ->map(fn ($line) => trim($line))
            ->filter(fn ($line) => $line !== '')
            ->values()
            ->all();
    }

    /**
     * The default OG image for this site (logo or configured image).
     */
    public function defaultOgImage(): ?string
    {
        return $this->og_image ?: ($this->logo_url ?: null);
    }

    /**
     * Build a title for a given page title using the configured template.
     *
     * Template tokens: %title%, %site%. Falls back to "{title} | {site}".
     */
    public function titleTemplate(?string $pageTitle, ?string $siteName): string
    {
        $siteName = $siteName ?: ($this->site_title ?: 'Rosetta CMS');
        $pageTitle = trim((string) $pageTitle);

        $template = trim((string) $this->seo_title_template);

        if ($template !== '') {
            return strtr($template, [
                '%title%' => $pageTitle,
                '%site%' => $siteName,
            ]);
        }

        $suffix = trim((string) $this->seo_title_suffix);

        if ($suffix === '') {
            return $pageTitle !== '' ? $pageTitle : $siteName;
        }

        if ($pageTitle === '') {
            return $siteName;
        }

        return $pageTitle . ' ' . $suffix;
    }
}
