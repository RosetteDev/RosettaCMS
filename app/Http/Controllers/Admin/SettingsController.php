<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Site;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display settings page.
     */
    public function index()
    {
        $site = Site::first();
        $settings = SiteSetting::forSite($site);

        $pages = Post::where('post_type', 'page')
            ->where('status', 'published')
            ->orderBy('title')
            ->get();

        return view('admin.settings.index', compact('settings', 'pages', 'site'));
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            // Site information
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string',
            'admin_email' => 'required|email|max:255',

            // Display
            'posts_per_page' => 'nullable|integer|min:1|max:100',
            'theme' => 'nullable|string|max:255',
            'font_family' => 'nullable|string|max:255',
            'container_width' => 'nullable|string|max:20',

            // Permalinks
            'permalink_structure' => 'nullable|in:post_name,date_and_name,numeric,custom',
            'custom_permalink' => 'nullable|string|max:255',

            // SEO
            'seo_title_suffix' => 'nullable|string|max:255',
            'seo_default_keywords' => 'nullable|string',
            'enable_xml_sitemap' => 'nullable|boolean',

            // Site code and maintenance
            'analytics_code' => 'nullable|string|max:20000',
            'custom_css' => 'nullable|string|max:200000',
            'custom_js' => 'nullable|string|max:200000',
            'maintenance_mode' => 'nullable|boolean',

            // Security
            'enable_debug_mode' => 'nullable|boolean',
            'disable_file_editor' => 'nullable|boolean',
            'max_upload_size' => 'nullable|integer|min:1|max:1000',
            'enable_user_registration' => 'nullable|boolean',

            // Comments
            'disable_comments' => 'nullable|boolean',
            'enable_comment_approval' => 'nullable|boolean',

            // Spam protection
            'akismet_api_key' => 'nullable|string|max:255',
            'enable_akismet' => 'nullable|boolean',
            'spam_keywords' => 'nullable|string',
            'rate_limit_enabled' => 'nullable|boolean',
            'rate_limit_per_minute' => 'nullable|integer|min:1|max:100',
            'captcha_enabled' => 'nullable|boolean',

            // Layout options
            'logo_url' => 'nullable|url',
            'logo_text' => 'nullable|string|max:255',
            'favicon_url' => 'nullable|url',
            'primary_color' => 'nullable|string|max:20',
            'secondary_color' => 'nullable|string|max:20',
            'accent_color' => 'nullable|string|max:20',
            'text_color' => 'nullable|string|max:20',
            'bg_color' => 'nullable|string|max:20',
            'border_color' => 'nullable|string|max:20',

            // E-commerce
            'tax_rate' => 'nullable|numeric|min:0|max:100',

            // Home page selection
            'home_post_id' => 'nullable|integer|exists:posts,id',
        ]);

        $site = Site::firstOrFail();

        // Persist settings for this site
        $settings = SiteSetting::firstOrNew(['site_id' => $site->id]);

        // Map form field names to the underlying schema where they differ.
        $settingsData = $validated;
        $settingsData['site_title'] = $validated['site_name'];
        $settingsData['post_per_page'] = $validated['posts_per_page'] ?? 10;
        unset($settingsData['site_name'], $settingsData['posts_per_page'], $settingsData['home_post_id'], $settingsData['theme']);

        $settings->fill($settingsData);
        $settings->site_id = $site->id;
        $settings->save();

        // Site-level theme settings and homepage
        $site->home_post_id = $validated['home_post_id'] ?? null;

        if (!empty($validated['theme'])) {
            $site->theme = $validated['theme'];
        }

        $site->theme_options = json_encode([
            'primary_color' => $validated['primary_color'] ?? '#3b82f6',
            'secondary_color' => $validated['secondary_color'] ?? '#6b7280',
            'accent_color' => $validated['accent_color'] ?? '#f59e0b',
            'text_color' => $validated['text_color'] ?? '#1f2937',
            'bg_color' => $validated['bg_color'] ?? '#ffffff',
            'border_color' => $validated['border_color'] ?? '#e5e7eb',
            'font_family' => $validated['font_family'] ?? 'system-ui, sans-serif',
            'container_width' => $validated['container_width'] ?? '1200px',
        ]);
        $site->save();

        return back()->with('success', 'Settings updated successfully.');
    }
}
