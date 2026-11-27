<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Media;
use App\Models\Menu;
use App\Models\MenuItem;
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
        $site = $this->site();
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
                'media' => url('/api/media'),
                'menus' => url('/api/menus'),
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
        $categories = $this->scoped(Category::query())
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
        $tags = $this->scoped(Tag::query())
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
     * Paginated, filterable media listing.
     */
    public function media(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'folder' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:image,video,audio,document'],
            'search' => ['nullable', 'string', 'max:200'],
        ]);

        $query = $this->scoped(Media::query());

        if (!empty($validated['folder'])) {
            $query->where('folder', $validated['folder']);
        }

        if (!empty($validated['type'])) {
            $this->applyMediaType($query, $validated['type']);
        }

        if (!empty($validated['search'])) {
            $term = $validated['search'];
            $query->where(function ($q) use ($term) {
                $q->where('file_name', 'like', '%' . $term . '%')
                    ->orWhere('alt_text', 'like', '%' . $term . '%')
                    ->orWhere('caption', 'like', '%' . $term . '%');
            });
        }

        $media = $query
            ->orderByDesc('created_at')
            ->paginate($validated['per_page'] ?? 10)
            ->withQueryString();

        return response()->json([
            'data' => collect($media->items())->map(fn (Media $item) => $this->mediaTransform($item)),
            'meta' => [
                'current_page' => $media->currentPage(),
                'per_page' => $media->perPage(),
                'total' => $media->total(),
                'last_page' => $media->lastPage(),
            ],
            'links' => [
                'first' => $media->url(1),
                'prev' => $media->previousPageUrl(),
                'next' => $media->nextPageUrl(),
                'last' => $media->url($media->lastPage()),
            ],
        ]);
    }

    /**
     * A single media item by id.
     */
    public function mediaShow(int $id): JsonResponse
    {
        $media = $this->scoped(Media::query())->findOrFail($id);

        return response()->json(['data' => $this->mediaTransform($media)]);
    }

    /**
     * Paginated-free menu listing with item counts.
     */
    public function menus(): JsonResponse
    {
        $menus = $this->scoped(Menu::query())
            ->withCount('items')
            ->orderBy('name')
            ->get()
            ->map(fn (Menu $menu) => $this->menuTransform($menu, false));

        return response()->json(['data' => $menus]);
    }

    /**
     * A single menu by slug, with its full item tree.
     */
    public function menu(string $slug): JsonResponse
    {
        $menu = $this->scoped(Menu::query())
            ->where('slug', $slug)
            ->with('items')
            ->firstOrFail();

        return response()->json(['data' => $this->menuTransform($menu, true)]);
    }

    /**
     * Restrict a media query to the given mime family.
     */
    protected function applyMediaType($query, string $type): void
    {
        match ($type) {
            'image' => $query->where('mime_type', 'like', 'image/%'),
            'video' => $query->where('mime_type', 'like', 'video/%'),
            'audio' => $query->where('mime_type', 'like', 'audio/%'),
            'document' => $query->where('mime_type', 'not like', 'image/%')
                ->where('mime_type', 'not like', 'video/%')
                ->where('mime_type', 'not like', 'audio/%'),
            default => null,
        };
    }

    /**
     * Shape a media record for the public JSON API.
     */
    protected function mediaTransform(Media $media): array
    {
        return [
            'id' => $media->id,
            'file_name' => $media->file_name,
            'url' => $media->url,
            'folder' => $media->folder,
            'mime_type' => $media->mime_type,
            'file_size' => $media->file_size,
            'human_size' => $media->human_size,
            'alt_text' => $media->alt_text,
            'caption' => $media->caption,
            'is_image' => $media->is_image,
            'created_at' => $media->created_at?->toIso8601String(),
        ];
    }

    /**
     * Shape a menu for the public JSON API.
     */
    protected function menuTransform(Menu $menu, bool $withItems = false): array
    {
        $data = [
            'id' => $menu->id,
            'name' => $menu->name,
            'slug' => $menu->slug,
            'location' => $menu->location,
            'item_count' => $menu->items_count ?? $menu->items->count(),
        ];

        if ($withItems) {
            $data['items'] = $this->menuTree($menu->items);
        }

        return $data;
    }

    /**
     * Build a nested tree of menu items from a flat collection.
     */
    protected function menuTree($items): array
    {
        $items = $items->keyBy('id');
        $roots = $items->whereNull('parent_id')->values();

        $build = function ($item) use (&$build, $items) {
            $children = $items->where('parent_id', $item->id)->sortBy('order')->values();

            return [
                'id' => $item->id,
                'title' => $item->title,
                'type' => $item->type,
                'url' => $item->resolved_url,
                'target' => $item->target,
                'order' => $item->order,
                'children' => $children->map(fn ($child) => $build($child))->values(),
            ];
        };

        return $roots->sortBy('order')->map(fn ($item) => $build($item))->values()->all();
    }

    /**
     * WordPress-compatible REST API root.
     */
    public function wordpressIndex(): JsonResponse
    {
        $site = $this->site();
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
            $this->scoped(Category::query())
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
            $this->scoped(Tag::query())
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
        return $this->scoped(Post::query())
            ->published()
            ->where('post_type', $type)
            ->with(['user', 'categories', 'tags']);
    }

    /**
     * Resolve the site for the current request.
     */
    protected function site(): ?Site
    {
        return Site::where('domain', request()->getHost())->first() ?: Site::first();
    }

    /**
     * Constrain a query to the current site when one is resolved.
     */
    protected function scoped($query)
    {
        $site = $this->site();

        if ($site) {
            $query->where($query->getModel()->getTable() . '.site_id', $site->id);
        }

        return $query;
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
