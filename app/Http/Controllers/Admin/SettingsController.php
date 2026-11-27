<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\LanguageContent;
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

        $translationLanguages = Language::where('is_active', true)
            ->where('is_default', false)
            ->orderBy('sort_order')
            ->get();

        $existingTranslations = $site
            ? LanguageContent::where('content_type', 'site_setting')
                ->where('content_id', $site->id)
                ->get()
                ->keyBy('language_id')
            : collect();

        return view('admin.settings.index', compact('settings', 'pages', 'site', 'translationLanguages', 'existingTranslations'));
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
            'body_font_size' => 'nullable|string|max:20',
            'line_height' => 'nullable|string|max:10',
            'block_gap' => 'nullable|string|max:20',
            'section_padding' => 'nullable|string|max:20',

            // Permalinks
            'permalink_structure' => 'nullable|in:post_name,date_and_name,numeric,custom',
            'custom_permalink' => 'nullable|string|max:255',

            // SEO
            'seo_title_suffix' => 'nullable|string|max:255',
            'seo_title_template' => 'nullable|string|max:255',
            'seo_default_keywords' => 'nullable|string',
            'og_image' => 'nullable|url|max:2048',
            'twitter_handle' => 'nullable|string|max:255',
            'verification_google' => 'nullable|string|max:255',
            'verification_bing' => 'nullable|string|max:255',
            'verification_pinterest' => 'nullable|string|max:255',
            'verification_yandex' => 'nullable|string|max:255',
            'robots_disallow' => 'nullable|string|max:10000',
            'robots_allow' => 'nullable|string|max:10000',
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

            // Custom form fields (JSON-encoded array)
            'custom_form_fields' => 'nullable|string|max:20000',
        ]);

        $customFields = $this->validateCustomFormFields($request->input('custom_form_fields'));

        $site = Site::firstOrFail();

        // Persist settings for this site
        $settings = SiteSetting::firstOrNew(['site_id' => $site->id]);

        // Map form field names to the underlying schema where they differ.
        $settingsData = $validated;
        $settingsData['site_title'] = $validated['site_name'];
        $settingsData['post_per_page'] = $validated['posts_per_page'] ?? 10;

        // Flatten the verification meta fields into the JSON map.
        $verification = array_filter([
            'google' => trim((string) ($validated['verification_google'] ?? '')),
            'bing' => trim((string) ($validated['verification_bing'] ?? '')),
            'pinterest' => trim((string) ($validated['verification_pinterest'] ?? '')),
            'yandex' => trim((string) ($validated['verification_yandex'] ?? '')),
        ], fn ($value) => $value !== '');

        $settingsData['verification_meta'] = $verification ?: null;
        $settingsData['custom_form_fields'] = $customFields ?: null;

        unset(
            $settingsData['site_name'],
            $settingsData['posts_per_page'],
            $settingsData['home_post_id'],
            $settingsData['theme'],
            $settingsData['verification_google'],
            $settingsData['verification_bing'],
            $settingsData['verification_pinterest'],
            $settingsData['verification_yandex']
        );

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
            'container_width' => $validated['container_width'] ?? ($settings->container_width ?? '1200px'),
            'body_font_size' => $validated['body_font_size'] ?? ($settings->body_font_size ?? '16px'),
            'line_height' => $validated['line_height'] ?? ($settings->line_height ?? '1.6'),
            'block_gap' => $validated['block_gap'] ?? ($settings->block_gap ?? '2rem'),
            'section_padding' => $validated['section_padding'] ?? ($settings->section_padding ?? '3rem'),
        ]);
        $site->save();

        $this->saveTranslations($request, $site);

        \App\Services\ContentCache::bump();

        return back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Validate the custom form fields JSON payload. Throws a validation
     * exception with a readable message on failure.
     */
    protected function validateCustomFormFields(?string $raw): array
    {
        if ($raw === null || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'custom_form_fields' => ['Custom form fields must be valid JSON.'],
            ]);
        }

        if (count($decoded) > 25) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'custom_form_fields' => ['A maximum of 25 custom form fields is allowed.'],
            ]);
        }

        $fields = [];

        foreach ($decoded as $index => $field) {
            if (!is_array($field)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'custom_form_fields' => ["Field #" . ($index + 1) . " must be an object."],
                ]);
            }

            $name = trim((string) ($field['name'] ?? ''));
            $type = (string) ($field['type'] ?? 'text');

            if ($name === '' || !preg_match('/^[a-z0-9_]{1,64}$/i', $name)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'custom_form_fields' => ["Field #" . ($index + 1) . ": name must be a slug (letters, numbers, underscores)."],
                ]);
            }

            if (!in_array($type, ['text', 'email', 'textarea', 'number'], true)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'custom_form_fields' => ["Field #" . ($index + 1) . ": unsupported type '{$type}'."],
                ]);
            }

            $label = trim((string) ($field['label'] ?? '')) ?: $name;
            $placeholder = trim((string) ($field['placeholder'] ?? ''));

            if (mb_strlen($label) > 255 || mb_strlen($placeholder) > 255) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'custom_form_fields' => ["Field #" . ($index + 1) . ": label and placeholder must be 255 characters or fewer."],
                ]);
            }

            $fields[] = [
                'name' => strtolower($name),
                'label' => $label,
                'type' => $type,
                'placeholder' => $placeholder,
                'required' => (bool) ($field['required'] ?? false),
            ];
        }

        // Reject duplicate names.
        $names = array_column($fields, 'name');
        if (count($names) !== count(array_unique($names))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'custom_form_fields' => ['Field names must be unique.'],
            ]);
        }

        return $fields;
    }

    /**
     * Persist translated site title/description for each enabled (non-default)
     * language. Stored in language_content (content_type='site_setting'), with
     * the per-field translations carried in the meta column.
     */
    protected function saveTranslations(Request $request, Site $site): void
    {
        $translations = $request->input('translations', []);

        if (!is_array($translations)) {
            return;
        }

        foreach ($translations as $languageId => $fields) {
            $language = Language::find($languageId);

            if (!$language || !$language->is_active || $language->is_default) {
                continue;
            }

            $fields = is_array($fields) ? $fields : [];
            $title = trim((string) ($fields['title'] ?? ''));
            $description = trim((string) ($fields['description'] ?? ''));

            $existing = LanguageContent::where('content_type', 'site_setting')
                ->where('content_id', $site->id)
                ->where('language_id', $language->id)
                ->first();

            if ($title === '' && $description === '') {
                $existing?->delete();
                continue;
            }

            $meta = array_filter([
                'title' => $title !== '' ? $title : null,
                'description' => $description !== '' ? $description : null,
            ], fn ($v) => $v !== null);

            $payload = [
                'content_type' => 'site_setting',
                'content_id' => $site->id,
                'language_id' => $language->id,
                'title' => $title !== '' ? $title : null,
                'excerpt' => null,
                'meta' => $meta,
                'translated_by' => auth()->id(),
            ];

            if ($existing) {
                $existing->update($payload);
            } else {
                LanguageContent::create($payload);
            }
        }
    }
}
