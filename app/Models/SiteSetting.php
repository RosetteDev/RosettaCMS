<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Get the settings record for a site (or the default site), creating an
     * unsaved instance when none exists yet.
     */
    public static function forSite(?Site $site = null): self
    {
        $site = $site ?: Site::first();

        if (!$site) {
            return new static();
        }

        return static::firstOrNew(['site_id' => $site->id]);
    }
}
