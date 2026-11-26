<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Traits\SanitizeContent;

/**
 * Write endpoints for the public REST API.
 *
 * Read endpoints live in ContentController; this controller adds create,
 * update and delete for posts and pages. All mutating routes require an
 * API key with the matching scope (posts:write / pages:write).
 */
class WriteController extends ContentController
{
    use SanitizeContent;

    /**
     * Create a post.
     */
    public function storePost(Request $request): JsonResponse
    {
        return $this->store($request, 'post');
    }

    /**
     * Update a post.
     */
    public function updatePost(Request $request, string $slug): JsonResponse
    {
        return $this->update($request, 'post', $slug);
    }

    /**
     * Delete a post.
     */
    public function destroyPost(string $slug): JsonResponse
    {
        return $this->destroy('post', $slug);
    }

    /**
     * Create a page.
     */
    public function storePage(Request $request): JsonResponse
    {
        return $this->store($request, 'page');
    }

    /**
     * Update a page.
     */
    public function updatePage(Request $request, string $slug): JsonResponse
    {
        return $this->update($request, 'page', $slug);
    }

    /**
     * Delete a page.
     */
    public function destroyPage(string $slug): JsonResponse
    {
        return $this->destroy('page', $slug);
    }

    /**
     * Shared create logic.
     */
    protected function store(Request $request, string $type): JsonResponse
    {
        $apiKey = $request->attributes->get('apiKey');
        $site = $this->site();

        $validated = $this->validateContent($request, $type, null);

        $post = new Post();
        $post->user_id = $apiKey?->user_id;
        $post->site_id = $site?->id;

        $this->fillPost($post, $validated, $type);

        $post->save();

        if ($type === 'post') {
            $post->categories()->sync($validated['categories'] ?? []);
            $post->tags()->sync($validated['tags'] ?? []);
        }

        return response()->json([
            'data' => $this->transform($post->load(['user', 'categories', 'tags'])),
        ], 201);
    }

    /**
     * Shared update logic.
     */
    protected function update(Request $request, string $type, string $slug): JsonResponse
    {
        $site = $this->site();
        $post = Post::where('post_type', $type)
            ->where('slug', $slug)
            ->when($site, fn ($q) => $q->where('site_id', $site->id))
            ->firstOrFail();

        $validated = $this->validateContent($request, $type, $post);

        $this->fillPost($post, $validated, $type);

        $post->save();

        if ($type === 'post' && array_key_exists('categories', $validated)) {
            $post->categories()->sync($validated['categories'] ?? []);
        }

        if ($type === 'post' && array_key_exists('tags', $validated)) {
            $post->tags()->sync($validated['tags'] ?? []);
        }

        return response()->json([
            'data' => $this->transform($post->load(['user', 'categories', 'tags'])),
        ]);
    }

    /**
     * Shared delete logic.
     */
    protected function destroy(string $type, string $slug): JsonResponse
    {
        $site = $this->site();
        $post = Post::where('post_type', $type)
            ->where('slug', $slug)
            ->when($site, fn ($q) => $q->where('site_id', $site->id))
            ->firstOrFail();

        $post->delete();

        return response()->json(['deleted' => true, 'slug' => $slug, 'type' => $type]);
    }

    /**
     * Validate content input for a post or page.
     */
    protected function validateContent(Request $request, string $type, ?Post $post): array
    {
        $site = $this->site();

        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable', 'string', 'max:255', 'alpha_dash',
                Rule::unique('posts', 'slug')
                    ->where('site_id', $site?->id)
                    ->ignore($post?->id),
            ],
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|string|max:2048',
            'blocks' => 'nullable|array',
            'status' => 'nullable|in:draft,pending,published,private',
            'published_at' => 'nullable|date',
            'comment_status' => 'nullable|in:open,closed',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'focus_keyword' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|url|max:2048',
            'robots' => 'nullable|in:index,follow,noindex,nofollow',
            'og_image' => 'nullable|string|max:2048',
        ]);
    }

    /**
     * Fill a post from validated input.
     */
    protected function fillPost(Post $post, array $validated, string $type): void
    {
        $content = $validated['content'] ?? null;
        if ($content) {
            $content = $this->sanitizeContent($content);
        }

        $status = $validated['status'] ?? 'draft';

        $publishedAt = $validated['published_at'] ?? null;
        if (!$publishedAt && $status === 'published' && !$post->published_at) {
            $publishedAt = now();
        } elseif ($post->published_at && !$publishedAt) {
            $publishedAt = $post->published_at;
        }

        $meta = array_filter([
            'seo_title' => $validated['seo_title'] ?? null,
            'seo_description' => $validated['seo_description'] ?? null,
            'focus_keyword' => $validated['focus_keyword'] ?? null,
            'canonical_url' => $validated['canonical_url'] ?? null,
            'robots' => $validated['robots'] ?? null,
            'og_image' => $validated['og_image'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        $post->fill([
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? Str::slug($validated['title']),
            'content' => $content,
            'excerpt' => $validated['excerpt'] ?? null,
            'featured_image' => $validated['featured_image'] ?? null,
            'blocks' => $validated['blocks'] ?? null,
            'meta' => $meta ?: null,
            'post_type' => $type,
            'status' => $status,
            'published_at' => $publishedAt,
            'comment_status' => $validated['comment_status'] ?? 'open',
        ]);
    }
}
