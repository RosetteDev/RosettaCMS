<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Traits\SanitizeContent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Post;
use App\Models\Category;
use App\Models\Language;
use App\Models\LanguageContent;
use App\Models\Tag;

class PostController extends Controller
{
    use SanitizeContent;

    /**
     * Display a listing of posts.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $posts = Post::where('post_type', 'post')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%')
                        ->orWhere('content', 'like', '%' . $search . '%');
                });
            })
            ->with('user', 'site')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $settings = SiteSetting::forSite();
        $users = \App\Models\User::orderBy('name')->get();

        return view('admin.posts.index', compact('posts', 'settings', 'users'));
    }

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        return view('admin.posts.form', [
            'action' => 'Create',
            'post' => new Post(),
            'postType' => 'post',
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created post.
     */
    public function store(Request $request)
    {
        $post = new Post();
        $post->user_id = auth()->id();

        $this->savePost($request, $post, 'post');

        return redirect()->route('admin.posts.edit', $post->id)
            ->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified post.
     */
    public function show(Post $post)
    {
        if ($post->post_type !== 'post') {
            return redirect()->route('admin.posts.index')
                ->with('error', 'This is not a post.');
        }

        return view('admin.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Post $post)
    {
        if ($post->post_type !== 'post') {
            return redirect()->route('admin.posts.index')
                ->with('error', 'This is not a post.');
        }

        return view('admin.posts.form', [
            'action' => 'Edit',
            'post' => $post,
            'postType' => 'post',
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, Post $post)
    {
        if ($post->post_type !== 'post') {
            return redirect()->route('admin.posts.index')
                ->with('error', 'This is not a post.');
        }

        $this->savePost($request, $post, 'post');

        return redirect()->route('admin.posts.edit', $post->id)
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified post.
     */
    public function destroy(Post $post)
    {
        if ($post->post_type !== 'post') {
            return redirect()->route('admin.posts.index')
                ->with('error', 'This is not a post.');
        }

        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Handle bulk operations on posts
     */
    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|exists:posts,id',
            'action' => 'required|in:delete,publish,unpublish,trash,restore,assign',
            'assign_user_id' => 'nullable|exists:users,id',
        ]);

        if ($validated['action'] === 'assign' && empty($validated['assign_user_id'])) {
            return response()->json(['success' => false, 'message' => 'Choose an author to assign.'], 422);
        }

        $posts = Post::whereIn('id', $validated['ids'])->get();

        switch ($validated['action']) {
            case 'delete':
                foreach ($posts as $post) {
                    $post->delete();
                }
                return response()->json(['success' => true, 'message' => count($posts) . ' posts permanently deleted.']);
            
            case 'publish':
                foreach ($posts as $post) {
                    $post->status = 'published';
                    if (!$post->published_at) {
                        $post->published_at = now();
                    }
                    $post->save();
                }
                return response()->json(['success' => true, 'message' => count($posts) . ' posts published.']);
            
            case 'unpublish':
                foreach ($posts as $post) {
                    $post->status = 'draft';
                    $post->published_at = null;
                    $post->save();
                }
                return response()->json(['success' => true, 'message' => count($posts) . ' posts unpublished (drafted).']);
            
            case 'trash':
                foreach ($posts as $post) {
                    $post->status = 'trash';
                    $post->save();
                }
                return response()->json(['success' => true, 'message' => count($posts) . ' posts moved to trash.']);
            
            case 'assign':
                foreach ($posts as $post) {
                    $post->user_id = $validated['assign_user_id'];
                    $post->save();
                }
                return response()->json(['success' => true, 'message' => count($posts) . ' post(s) assigned to ' . \App\Models\User::find($validated['assign_user_id'])?->name . '.']);

            case 'restore':
                foreach ($posts as $post) {
                    $post->status = 'draft';
                    $post->save();
                }
                return response()->json(['success' => true, 'message' => count($posts) . ' posts restored from trash.']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action.'], 400);
    }

    /**
     * Validate and persist a post/page from the shared editor.
     */
    protected function savePost(Request $request, Post $post, string $type = 'post'): Post
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable', 'string', 'max:255', 'alpha_dash',
                Rule::unique('posts', 'slug')
                    ->where('site_id', $post->site_id ?? optional(\App\Models\Site::first())->id)
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

        $content = $validated['content'] ?? null;
        if ($content) {
            $content = $this->sanitizeContent($content);
        }

        $status = $validated['status'] ?? 'draft';

        // Determine the publish timestamp.
        $publishedAt = $validated['published_at'] ?? null;
        if (!$publishedAt && $status === 'published' && !$post->published_at) {
            $publishedAt = now();
        } elseif ($post->published_at && !$publishedAt) {
            $publishedAt = $post->published_at;
        }

        // A future publish date makes the post scheduled — it stays out of
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
            'site_id' => $validated['site_id'] ?? $post->site_id ?? optional(\App\Models\Site::first())->id,
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

        // Taxonomies only apply to posts.
        if ($type === 'post') {
            $post->categories()->sync($validated['categories'] ?? []);
            $post->tags()->sync($validated['tags'] ?? []);
        }

        $this->saveTranslations($request, $post, $type);

        \App\Services\ContentCache::bump();

        return $post;
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
