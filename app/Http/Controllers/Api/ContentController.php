<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Site;
use App\Models\SiteSetting;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    /**
     * API discovery document.
     */
    public function index(): JsonResponse
    {
        $site = Site::first();
        $settings = SiteSetting::forSite($site);

        return response()->json([
            'name' => $settings->site_title ?? $site->name ?? 'Rosetta CMS',
            'description' => $settings->site_description ?? $site->description,
            'url' => url('/'),
            'version' => 'v1',
            'endpoints' => [
                'posts' => url('/api/posts'),
                'pages' => url('/api/pages'),
                'categories' => url('/api/categories'),
                'tags' => url('/api/tags'),
                'search' => url('/api/search?q=term'),
            ],
        ]);
    }

    /**
     * Published posts.
     */
    public function posts(Request $request): JsonResponse
    {
        return $this->listing($request, 'post');
    }

    /**
     * Published pages.
     */
    public function pages(Request $request): JsonResponse
    {
        return $this->listing($request, 'page');
    }

    /**
     * A single published post.
     */
    public function post(string $slug): JsonResponse
    {
        $post = $this->findPublished('post', $slug);

        return response()->json(['data' => $this->transform($post)]);
    }

    /**
     * A single published page.
     */
    public function page(string $slug): JsonResponse
    {
        $post = $this->findPublished('page', $slug);

        return response()->json(['data' => $this->transform($post)]);
    }

    /**
     * Categories with published post counts.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::query()
            ->withCount(['posts' => fn ($query) => $query->published()->where('post_type', 'post')])
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'url' => route('category.show', $category->slug),
                'post_count' => $category->posts_count,
            ]);

        return response()->json(['data' => $categories]);
    }

    /**
     * Tags with published post counts.
     */
    public function tags(): JsonResponse
    {
        $tags = Tag::query()
            ->withCount(['posts' => fn ($query) => $query->published()->where('post_type', 'post')])
            ->orderBy('name')
            ->get()
            ->map(fn (Tag $tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'description' => $tag->description,
                'url' => route('tag.show', $tag->slug),
                'post_count' => $tag->posts_count,
            ]);

        return response()->json(['data' => $tags]);
    }

    /**
     * Search published posts.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
        ]);

        $term = trim((string) $request->query('q', ''));

        if ($term === '') {
            return response()->json(['data' => [], 'meta' => ['query' => '']]);
        }

        $posts = $this->baseQuery('post')
            ->where(function ($query) use ($term) {
                $query->where('title', 'like', '%' . $term . '%')
                    ->orWhere('excerpt', 'like', '%' . $term . '%')
                    ->orWhere('content', 'like', '%' . $term . '%');
            })
            ->orderByDesc('published_at')
            ->limit(50)
            ->get()
            ->map(fn (Post $post) => $this->transform($post));

        return response()->json(['data' => $posts, 'meta' => ['query' => $term]]);
    }

    /**
     * WordPress-compatible REST API root.
     */
    public function wordpressIndex(): JsonResponse
    {
        $site = Site::first();
        $settings = SiteSetting::forSite($site);

        return response()->json([
            'name' => $settings->site_title ?? $site->name ?? 'Rosetta CMS',
            'description' => $settings->site_description ?? $site->description,
            'url' => url('/'),
            'home' => url('/'),
            'namespaces' => ['wp/v2'],
            'authentication' => [],
        ]);
    }

    /**
     * WordPress-compatible post collection.
     */
    public function wordpressPosts(Request $request): JsonResponse
    {
        return $this->wordpressListing($request, 'post');
    }

    /**
     * WordPress-compatible page collection.
     */
    public function wordpressPages(Request $request): JsonResponse
    {
        return $this->wordpressListing($request, 'page');
    }

    /**
     * WordPress-compatible single post.
     */
    public function wordpressPost(string $slug): JsonResponse
    {
        return response()->json($this->wordpressTransform($this->findPublished('post', $slug)));
    }

    /**
     * WordPress-compatible single page.
     */
    public function wordpressPage(string $slug): JsonResponse
    {
        return response()->json($this->wordpressTransform($this->findPublished('page', $slug)));
    }

    /**
     * WordPress-compatible category collection.
     */
    public function wordpressCategories(): JsonResponse
    {
        return response()->json(
            Category::query()
                ->withCount(['posts' => fn ($query) => $query->published()->where('post_type', 'post')])
                ->orderBy('name')
                ->get()
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description ?? '',
                    'parent' => 0,
                    'count' => $category->posts_count,
                    'link' => route('category.show', $category->slug),
                ])
                ->values()
        );
    }

    /**
     * WordPress-compatible tag collection.
     */
    public function wordpressTags(): JsonResponse
    {
        return response()->json(
            Tag::query()
                ->withCount(['posts' => fn ($query) => $query->published()->where('post_type', 'post')])
                ->orderBy('name')
                ->get()
                ->map(fn (Tag $tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                    'description' => $tag->description ?? '',
                    'count' => $tag->posts_count,
                    'link' => route('tag.show', $tag->slug),
                ])
                ->values()
        );
    }

    /**
     * Build a paginated JSON listing for the public API.
     */
    protected function listing(Request $request, string $type): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'category' => ['nullable', 'string', 'max:255'],
            'tag' => ['nullable', 'string', 'max:255'],
            'search' => ['nullable', 'string', 'max:200'],
        ]);

        $query = $this->baseQuery($type);

        if (!empty($validated['category'])) {
            $query->whereHas('categories', fn ($q) => $q->where('slug', $validated['category']));
        }

        if (!empty($validated['tag'])) {
            $query->whereHas('tags', fn ($q) => $q->where('slug', $validated['tag']));
        }

        if (!empty($validated['search'])) {
            $term = $validated['search'];
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', '%' . $term . '%')
                    ->orWhere('excerpt', 'like', '%' . $term . '%')
                    ->orWhere('content', 'like', '%' . $term . '%');
            });
        }

        $posts = $query
            ->orderByDesc('published_at')
            ->paginate($validated['per_page'] ?? 10)
            ->withQueryString();

        return response()->json([
            'data' => collect($posts->items())->map(fn (Post $post) => $this->transform($post)),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'last_page' => $posts->lastPage(),
            ],
            'links' => [
                'first' => $posts->url(1),
                'prev' => $posts->previousPageUrl(),
                'next' => $posts->nextPageUrl(),
                'last' => $posts->url($posts->lastPage()),
            ],
        ]);
    }

    /**
     * Build a WordPress-style collection response.
     */
    protected function wordpressListing(Request $request, string $type): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 10), 1), 100);

        $posts = $this->baseQuery($type)
            ->orderByDesc('published_at')
            ->paginate($perPage);

        $payload = collect($posts->items())
            ->map(fn (Post $post) => $this->wordpressTransform($post))
            ->values();

        return response()
            ->json($payload)
            ->header('X-WP-Total', (string) $posts->total())
            ->header('X-WP-TotalPages', (string) $posts->lastPage());
    }

    /**
     * Start a published content query for the given type.
     */
    protected function baseQuery(string $type)
    {
        return Post::query()
            ->published()
            ->where('post_type', $type)
            ->with(['user', 'categories', 'tags']);
    }

    /**
     * Find a published post or page by slug.
     */
    protected function findPublished(string $type, string $slug): Post
    {
        return $this->baseQuery($type)->where('slug', $slug)->firstOrFail();
    }

    /**
     * Shape a post for the public JSON API.
     */
    protected function transform(Post $post): array
    {
        $meta = $post->meta ?? [];

        return [
            'id' => $post->id,
            'type' => $post->post_type,
            'slug' => $post->slug,
            'status' => $post->status,
            'title' => $post->title,
            'excerpt' => $post->plain_excerpt,
            'content' => $post->rendered_content,
            'url' => $post->public_url,
            'featured_image' => $post->featured_image_url,
            'author' => $post->user ? [
                'id' => $post->user->id,
                'name' => $post->user->name,
            ] : null,
            'categories' => $post->categories->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])->values(),
            'tags' => $post->tags->map(fn (Tag $tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ])->values(),
            'seo' => [
                'title' => $meta['seo_title'] ?? $post->title,
                'description' => $meta['seo_description'] ?? $post->plain_excerpt,
                'canonical' => $meta['canonical_url'] ?? $post->public_url,
                'robots' => $meta['robots'] ?? 'index',
            ],
            'published_at' => $post->published_at?->toIso8601String(),
            'updated_at' => $post->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Shape a post using the WordPress REST API field names.
     */
    protected function wordpressTransform(Post $post): array
    {
        $excerpt = $post->plain_excerpt;

        return [
            'id' => $post->id,
            'date' => $post->published_at?->toIso8601String(),
            'date_gmt' => $post->published_at?->utc()->toIso8601String(),
            'guid' => ['rendered' => $post->public_url],
            'modified' => $post->updated_at?->toIso8601String(),
            'modified_gmt' => $post->updated_at?->utc()->toIso8601String(),
            'slug' => $post->slug,
            'status' => 'publish',
            'type' => $post->post_type,
            'link' => $post->public_url,
            'title' => ['rendered' => e($post->title)],
            'content' => ['rendered' => $post->rendered_content, 'protected' => false],
            'excerpt' => ['rendered' => $excerpt === '' ? '' : '<p>' . e($excerpt) . '</p>', 'protected' => false],
            'author' => $post->user_id,
            'featured_media' => 0,
            'comment_status' => $post->comment_status ?? 'open',
            'categories' => $post->categories->pluck('id')->values(),
            'tags' => $post->tags->pluck('id')->values(),
        ];
    }
}
