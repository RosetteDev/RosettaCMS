<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Category;
use App\Models\Language;
use App\Models\LanguageContent;
use App\Models\Post;
use App\Models\Site;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/**
 * Shared validation/persistence logic for saving a post-like record
 * (post, portfolio item, testimonial, event, …).
 */
trait SavesPostContent
{
    /**
     * Validate and persist a post of the given type.
     */
    protected function savePost(Request $request, Post $post, string $type): Post
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable', 'string', 'max:255', 'alpha_dash',
                Rule::unique('posts', 'slug')
                    ->where('site_id', $post->site_id ?? optional(Site::first())->id)
                    ->ignore($post->id),
            ],
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|string|max:2048',
            'status' => 'nullable|in:draft,pending,published,private',
            'published_at' => 'nullable|date',
            'comment_status' => 'nullable|in:open,closed',
            'expires_at' => 'nullable|date',
            'repeat_frequency' => 'nullable|in:daily,weekly,monthly',
            'repeat_until' => 'nullable|date',
            'site_id' => 'nullable|exists:sites,id',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',

            // Custom fields (stored in meta['custom_fields'])
            'custom_fields' => 'nullable|array|max:50',
            'custom_fields.*.key' => 'nullable|string|max:100|regex:/^[a-z][a-z0-9_]*$/',
            'custom_fields.*.value' => 'nullable|string|max:1000',

            // SEO
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'focus_keyword' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|url|max:2048',
            'robots' => 'nullable|in:index,follow,noindex,nofollow',
            'og_image' => 'nullable|string|max:2048',

            // Translations
            'translations' => 'nullable|array',
            'translations.*.title' => 'nullable|string|max:255',
            'translations.*.excerpt' => 'nullable|string|max:1000',
            'translations.*.content' => 'nullable|string',
            'translations.*.slug' => 'nullable|string|max:255',
        ]);

        // Respect per-field permissions: restore the stored values for any
        // field the current user is not allowed to edit.
        $validated = $this->filterEditablePayload($validated, $post, $type);

        $content = $validated['content'] ?? null;
        if ($content) {
            $content = $this->sanitizeContent($content);
        }

        $status = $validated['status'] ?? 'draft';

        // A role without publish permission cannot publish the record.
        if (!auth()->user()->canPerform($type, 'publish') && $status === 'published') {
            $status = 'draft';
        }

        // Determine the publish timestamp.
        $publishedAt = $validated['published_at'] ?? null;
        if (!$publishedAt && $status === 'published' && !$post->published_at) {
            $publishedAt = now();
        } elseif ($post->published_at && !$publishedAt) {
            $publishedAt = $post->published_at;
        }

        // A future publish date makes the record scheduled — it stays out of
        // the live frontend until the scheduler publishes it.
        if ($publishedAt && \Carbon\Carbon::parse($publishedAt)->isFuture()) {
            $status = 'pending';
        }

        // Build the custom fields meta payload (drops empty keys).
        $customFields = [];
        foreach ($validated['custom_fields'] ?? [] as $field) {
            $key = trim($field['key'] ?? '');
            $value = trim($field['value'] ?? '');
            if ($key !== '' && $value !== '') {
                $customFields[$key] = $value;
            }
        }

        // Build the SEO meta payload (drops empty values).
        $meta = array_filter([
            'seo_title' => $validated['seo_title'] ?? null,
            'seo_description' => $validated['seo_description'] ?? null,
            'focus_keyword' => $validated['focus_keyword'] ?? null,
            'canonical_url' => $validated['canonical_url'] ?? null,
            'robots' => $validated['robots'] ?? null,
            'og_image' => $validated['og_image'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        if ($customFields !== []) {
            $meta['custom_fields'] = $customFields;
        }

        $post->fill([
            'site_id' => $validated['site_id'] ?? $post->site_id ?? optional(Site::first())->id,
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? Str::slug($validated['title']),
            'content' => $content,
            'excerpt' => $validated['excerpt'] ?? null,
            'featured_image' => $validated['featured_image'] ?? null,
            'meta' => $meta ?: null,
            'post_type' => $type,
            'status' => $status,
            'published_at' => $publishedAt,
            'comment_status' => $validated['comment_status'] ?? 'open',
            'expires_at' => $validated['expires_at'] ?? null,
            'repeat_frequency' => $validated['repeat_frequency'] ?? null,
            'repeat_until' => $validated['repeat_until'] ?? null,
        ]);

        $post->save();

        // Taxonomies only apply to the post type (custom types skip them).
        if ($type === 'post') {
            $post->categories()->sync($validated['categories'] ?? []);
            $post->tags()->sync($validated['tags'] ?? []);
        }

        $request->merge(['translations' => $validated['translations'] ?? []]);

        $this->saveTranslations($request, $post, $type);

        \App\Services\ContentCache::bump();

        return $post;
    }

    /**
     * Restore the stored values for fields the current user may not edit.
     *
     * Returns a copy of the validated payload with those fields taken from
     * the stored post (so a submit can never silently wipe them).
     */
    protected function filterEditablePayload(array $validated, Post $post, string $type): array
    {
        $editable = auth()->user()->editableFieldsFor($type);

        $metaFields = ['seo_title', 'seo_description', 'focus_keyword', 'robots', 'canonical_url', 'og_image'];

        foreach (['title', 'slug', 'content', 'excerpt', 'featured_image'] as $field) {
            if (!in_array($field, $editable, true)) {
                $validated[$field] = $post->{$field} ?? null;
            }
        }

        foreach ($metaFields as $field) {
            if (!in_array($field, $editable, true)) {
                $validated[$field] = $post->meta[$field] ?? null;
            }
        }

        if (!in_array('status', $editable, true)) {
            $validated['status'] = $post->status ?? 'draft';
        }

        if (!in_array('published_at', $editable, true)) {
            $validated['published_at'] = $post->published_at;
        }

        if (!in_array('custom_fields', $editable, true)) {
            $validated['custom_fields'] = $post->meta['custom_fields'] ?? [];
        }

        if (!in_array('translations', $editable, true)) {
            $validated['translations'] = [];
        }

        return $validated;
    }

    /**
     * Persist translated title/excerpt/content for each enabled language.
     *
     * Empty translation fields remove the stored translation; a fully
     * populated translation upserts by (content_type, content_id, language).
     */
    protected function saveTranslations(Request $request, Post $post, string $type): void
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
            $excerpt = trim((string) ($fields['excerpt'] ?? ''));
            $content = trim((string) ($fields['content'] ?? ''));
            $slug = trim((string) ($fields['slug'] ?? ''));

            $existing = LanguageContent::where('content_type', $type)
                ->where('content_id', $post->id)
                ->where('language_id', $language->id)
                ->first();

            if ($title === '' && $excerpt === '' && $content === '' && $slug === '') {
                $existing?->delete();
                continue;
            }

            $payload = [
                'content_type' => $type,
                'content_id' => $post->id,
                'language_id' => $language->id,
                'title' => $title !== '' ? $title : null,
                'excerpt' => $excerpt !== '' ? $excerpt : null,
                'slug' => $slug !== '' ? $slug : null,
                'content' => $content !== '' ? $this->sanitizeContent($content) : null,
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
