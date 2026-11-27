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
     * Sections available in the settings area, keyed by route segment.
     */
    public const SECTIONS = [
        'general' => 'General',
        'seo' => 'Content & SEO',
        'security' => 'Security & Trust',
        'commerce' => 'Commerce',
        'site-code' => 'Site Code',
    ];

    /**
     * Field ownership per section. Only these fields are validated and persisted
     * when the section is submitted; every other setting is left untouched.
     */
    protected const SECTION_FIELDS = [
        'general' => [
            'site_name', 'site_description', 'admin_email', 'home_post_id',
            'posts_per_page', 'theme',
            'font_family', 'container_width', 'body_font_size', 'line_height',
            'block_gap', 'section_padding',
            'logo_url', 'logo_text', 'favicon_url',
            'primary_color', 'secondary_color', 'accent_color',
            'text_color', 'bg_color', 'border_color',
        ],
        'seo' => [
            'permalink_structure', 'custom_permalink',
            'seo_title_suffix', 'seo_title_template', 'seo_default_keywords',
            'og_image', 'twitter_handle',
            'verification_google', 'verification_bing',
            'verification_pinterest', 'verification_yandex',
            'robots_disallow', 'robots_allow', 'enable_xml_sitemap',
            'custom_form_fields',
        ],
        'security' => [
            'disable_comments', 'enable_comment_approval',
            'akismet_api_key', 'enable_akismet', 'spam_keywords',
            'rate_limit_enabled', 'rate_limit_per_minute', 'captcha_enabled',
            'enable_debug_mode', 'disable_file_editor', 'max_upload_size',
            'enable_user_registration', 'enable_welcome_email',
            'enable_booking_form', 'enable_chat_widget',
            'enable_social_login_google', 'enable_social_login_github',
        ],
        'commerce' => ['tax_rate'],
        'site-code' => ['analytics_code', 'custom_css', 'custom_js', 'maintenance_mode'],
    ];

    /**
     * Display a single settings section.
     */
    public function edit(string $section)
    {
        if (!array_key_exists($section, self::SECTIONS)) {
            abort(404);
        }

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

        return view('admin.settings.show', compact(
            'settings', 'pages', 'site', 'translationLanguages', 'existingTranslations', 'section'
        ))->with('sections', self::SECTIONS);
    }

    /**
     * Validate and persist only the fields owned by the given section.
     */
    public function update(Request $request, string $section)
    {
        if (!array_key_exists($section, self::SECTIONS)) {
            abort(404);
        }

        $validated = $request->validate($this->sectionRules($section));

        $site = Site::firstOrFail();
        $settings = SiteSetting::firstOrNew(['site_id' => $site->id]);

        // Map form field names to the underlying schema where they differ.
        $settingsData = $validated;

        if (array_key_exists('site_name', $validated)) {
            $settingsData['site_title'] = $validated['site_name'];
            unset($settingsData['site_name']);
        }

        if (array_key_exists('posts_per_page', $validated)) {
            $settingsData['post_per_page'] = $validated['posts_per_page'] ?? 10;
            unset($settingsData['posts_per_page']);
        }

        // Flatten the verification meta fields into the JSON map.
        $verificationKeys = ['verification_google', 'verification_bing', 'verification_pinterest', 'verification_yandex'];
        if (array_intersect_key($validated, array_flip($verificationKeys))) {
            $verification = array_filter([
                'google' => trim((string) ($validated['verification_google'] ?? '')),
                'bing' => trim((string) ($validated['verification_bing'] ?? '')),
                'pinterest' => trim((string) ($validated['verification_pinterest'] ?? '')),
                'yandex' => trim((string) ($validated['verification_yandex'] ?? '')),
            ], fn ($value) => $value !== '');

            $settingsData['verification_meta'] = $verification ?: null;

            foreach ($verificationKeys as $key) {
                unset($settingsData[$key]);
            }
        }

        if (array_key_exists('custom_form_fields', $validated)) {
            $customFields = $this->validateCustomFormFields($request->input('custom_form_fields'));
            $settingsData['custom_form_fields'] = $customFields ?: null;
        }

        // Site-level fields are not stored on the settings row.
        unset($settingsData['home_post_id'], $settingsData['theme']);

        $settings->fill($settingsData);
        $settings->site_id = $site->id;
        $settings->save();

        // Site-level theme settings and homepage (only when present).
        if (array_key_exists('home_post_id', $validated)) {
            $site->home_post_id = $validated['home_post_id'] ?? null;
        }

        if (!empty($validated['theme'])) {
            $site->theme = $validated['theme'];
        }

        $themeKeys = [
            'primary_color', 'secondary_color', 'accent_color', 'text_color',
            'bg_color', 'border_color', 'font_family', 'container_width',
            'body_font_size', 'line_height', 'block_gap', 'section_padding',
        ];

        $themePresent = array_intersect_key($validated, array_flip($themeKeys));

        if (!empty($themePresent)) {
            $existing = json_decode($site->theme_options ?? '{}', true) ?: [];
            $site->theme_options = json_encode(array_merge($existing, $themePresent));
        }

        $site->save();

        if ($request->has('translations')) {
            $this->saveTranslations($request, $site);
        }

        \App\Services\ContentCache::bump();

        return back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Validation rules for the fields owned by the given section.
     */
    protected function sectionRules(string $section): array
    {
        $all = [
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
            'enable_welcome_email' => 'nullable|boolean',
            'enable_booking_form' => 'nullable|boolean',
            'enable_chat_widget' => 'nullable|boolean',
            'enable_social_login_google' => 'nullable|boolean',
            'enable_social_login_github' => 'nullable|boolean',

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
        ];

        $fields = self::SECTION_FIELDS[$section] ?? [];

        return array_intersect_key($all, array_flip($fields));
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
