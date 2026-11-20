<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Menu;
use App\Models\Post;
use App\Models\Site;
use App\Models\SiteSetting;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class FrontendController extends Controller
{
    protected ?Site $site;
    protected ?SiteSetting $settings;
    protected string $themeSlug;
    protected bool $themePreview = false;

    public function __construct(Request $request)
    {
        $this->site = Site::first();
        $this->settings = SiteSetting::forSite($this->site);
        $this->themeSlug = $this->site->theme ?? 'rosetta';

        // Staff can preview any installed theme without activating it.
        $preview = (string) $request->query('theme', '');

        if ($preview !== ''
            && preg_match('/^[A-Za-z0-9_-]+$/', $preview)
            && auth()->check()
            && auth()->user()->hasRole('admin')
            && file_exists(public_path('themes/' . $preview . '/css/style.css'))) {
            $this->themeSlug = $preview;
            $this->themePreview = true;
        }
    }

    /**
     * Public homepage.
     */
    public function home()
    {
        // A designated static homepage takes over the front page.
        if ($this->site && $this->site->home_post_id) {
            $homepage = $this->site->homepage;

            if ($homepage
                && $homepage->status === 'published'
                && (!$homepage->published_at || $homepage->published_at->isPast())) {
                return $this->render($homepage->post_type === 'page' ? 'page' : 'single', [
                    'post' => $homepage,
                    'page' => $homepage,
                    'relatedPosts' => collect(),
                    'seo' => $this->seoForPost($homepage),
                ]);
            }
        }

        $posts = Post::query()
            ->published()
            ->where('post_type', 'post')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->limit($this->settings->post_per_page ?: 10)
            ->get();

        return $this->render('home', [
            'posts' => $posts,
            'seo' => $this->seo([
                'title' => $this->settings->site_title ?: ($this->site->name ?? 'Rosetta CMS'),
                'description' => $this->settings->site_description ?: ($this->site->description ?? ''),
                'canonical' => url('/'),
            ]),
        ]);
    }

    /**
     * A single published post.
     */
    public function post(string $slug)
    {
        $preview = $this->wantsPreview();

        $query = Post::query()
            ->where('post_type', 'post')
            ->where('slug', $slug);

        if (!$preview) {
            $query->published();
        }

        $post = $query->firstOrFail();

        $commentsEnabled = !$this->settings->disable_comments;

        return $this->render('single', [
            'post' => $post,
            'relatedPosts' => $this->relatedPosts($post),
            'commentsEnabled' => $commentsEnabled,
            'comments' => $commentsEnabled ? $this->approvedComments($post) : collect(),
            'preview' => $preview,
            'seo' => $this->seoForPost($post, $preview),
        ]);
    }

    /**
     * Store a comment submitted from the public site.
     */
    public function storeComment(Request $request, string $slug)
    {
        $post = Post::query()
            ->published()
            ->where('post_type', 'post')
            ->where('slug', $slug)
            ->firstOrFail();

        if ($this->settings->disable_comments) {
            return back()->with('error', 'Comments are disabled on this site.');
        }

        $data = $request->validate([
            'author_name' => auth()->check() ? 'nullable|string|max:120' : 'required|string|max:120',
            'author_email' => auth()->check() ? 'nullable|email|max:180' : 'required|email|max:180',
            'author_url' => 'nullable|url|max:255',
            'content' => 'required|string|min:2|max:5000',
            'parent_id' => 'nullable|integer|exists:comments,id',
        ]);

        // Simple honeypot: bots fill hidden fields.
        if ($request->filled('website')) {
            return back()->with('error', 'Your comment could not be submitted.');
        }

        if ($this->looksLikeSpam($data['content'])) {
            return back()->with('error', 'Your comment was flagged as spam.')->withInput();
        }

        $parentId = null;
        if (!empty($data['parent_id'])) {
            $parent = Comment::approved()->where('post_id', $post->id)->find($data['parent_id']);
            $parentId = $parent?->id;
        }

        $requiresApproval = (bool) ($this->settings->enable_comment_approval ?? true);

        Comment::create([
            'post_id' => $post->id,
            'user_id' => auth()->id(),
            'parent_id' => $parentId,
            'content' => trim(strip_tags($data['content'])),
            'author_name' => auth()->user()?->name ?? $data['author_name'],
            'author_email' => auth()->user()?->email ?? $data['author_email'],
            'author_url' => auth()->check() ? null : ($data['author_url'] ?? null),
            'status' => $requiresApproval ? 'pending' : 'approved',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        $message = $requiresApproval
            ? 'Thanks! Your comment is awaiting moderation.'
            : 'Thanks! Your comment has been posted.';

        return redirect()->to($post->public_url . '#comments')->with('success', $message);
    }

    /**
     * Approved top-level comments with their approved replies.
     */
    protected function approvedComments(Post $post)
    {
        return $post->comments()
            ->approved()
            ->whereNull('parent_id')
            ->with(['replies' => fn ($query) => $query->approved()->oldest()])
            ->oldest()
            ->get();
    }

    /**
     * Very small keyword based spam check.
     */
    protected function looksLikeSpam(string $content): bool
    {
        $keywords = array_filter(array_map('trim', explode(',', (string) $this->settings->spam_keywords)));

        if (empty($keywords)) {
            return false;
        }

        $haystack = mb_strtolower($content);

        foreach ($keywords as $keyword) {
            if ($keyword !== '' && str_contains($haystack, mb_strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }

    /**
     * A single published page.
     */
    public function page(string $slug)
    {
        $preview = $this->wantsPreview();

        $query = Post::query()
            ->where('post_type', 'page')
            ->where('slug', $slug);

        if (!$preview) {
            $query->published();
        }

        $page = $query->firstOrFail();

        return $this->render('page', [
            'page' => $page,
            'post' => $page,
            'relatedPosts' => collect(),
            'preview' => $preview,
            'seo' => $this->seoForPost($page, $preview),
        ]);
    }

    /**
     * About page: prefer a CMS page, otherwise fall back to the theme's About.
     */
    public function about()
    {
        $page = Post::query()->published()->where('post_type', 'page')
            ->whereIn('slug', ['about', 'about-us', 'about-our-cms'])
            ->first();

        return $this->render('about', [
            'page' => $page,
            'post' => $page,
            'seo' => $this->seo([
                'title' => $page?->title ?: 'About Us',
                'description' => $this->settings->site_description,
                'canonical' => url('/about'),
            ]),
        ]);
    }

    /**
     * Contact page: prefer a CMS page, otherwise fall back to the theme's Contact.
     */
    public function contact()
    {
        $page = Post::query()->published()->where('post_type', 'page')
            ->whereIn('slug', ['contact', 'contact-us'])
            ->first();

        return $this->render('contact', [
            'page' => $page,
            'post' => $page,
            'seo' => $this->seo([
                'title' => $page?->title ?: 'Contact Us',
                'description' => $this->settings->site_description,
                'canonical' => url('/contact'),
            ]),
        ]);
    }

    /**
     * Category archive.
     */
    public function category(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $posts = $category->posts()
            ->published()
            ->where('post_type', 'post')
            ->orderByDesc('published_at')
            ->paginate($this->settings->post_per_page ?: 10);

        return $this->render('category', [
            'category' => $category,
            'posts' => $posts,
            'seo' => $this->seo([
                'title' => 'Category: ' . $category->name,
                'description' => $category->description ?: $this->settings->site_description,
                'canonical' => $this->pagedUrl('/categories/' . $category->slug, $posts),
            ]),
        ]);
    }

    /**
     * Tag archive.
     */
    public function tag(string $slug)
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $posts = $tag->posts()
            ->published()
            ->where('post_type', 'post')
            ->orderByDesc('published_at')
            ->paginate($this->settings->post_per_page ?: 10);

        return $this->render('tag', [
            'tag' => $tag,
            'posts' => $posts,
            'seo' => $this->seo([
                'title' => 'Tag: ' . $tag->name,
                'description' => $tag->description ?: $this->settings->site_description,
                'canonical' => $this->pagedUrl('/tags/' . $tag->slug, $posts),
            ]),
        ]);
    }

    /**
     * Blog archive.
     */
    public function blog()
    {
        $posts = Post::query()
            ->published()
            ->where('post_type', 'post')
            ->orderByDesc('published_at')
            ->paginate($this->settings->post_per_page ?: 10);

        return $this->render('category', [
            'category' => null,
            'posts' => $posts,
            'seo' => $this->seo([
                'title' => 'Blog',
                'description' => $this->settings->site_description,
                'canonical' => $this->pagedUrl('/blog', $posts),
            ]),
        ]);
    }

    /**
     * Search results.
     */
    public function search(Request $request)
    {
        $query = trim((string) $request->input('q'));

        $posts = Post::query()
            ->published()
            ->where('post_type', 'post')
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($builder) use ($query) {
                    $builder->where('title', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%")
                        ->orWhere('excerpt', 'like', "%{$query}%");
                });
            })
            ->orderByDesc('published_at')
            ->paginate($this->settings->post_per_page ?: 10);

        return $this->render('search', [
            'query' => $query,
            'posts' => $posts,
            'seo' => $this->seo([
                'title' => $query !== '' ? "Search: {$query}" : 'Search',
                'description' => $this->settings->site_description,
                'canonical' => url('/search'),
                'robots' => 'noindex',
            ]),
        ]);
    }

    /**
     * XML sitemap for posts, pages, categories and tags.
     */
    public function sitemap()
    {
        abort_unless((bool) ($this->settings->enable_xml_sitemap ?? true), 404);

        $posts = Post::query()
            ->published()
            ->where('post_type', 'post')
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at']);

        $pages = Post::query()
            ->published()
            ->where('post_type', 'page')
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at']);

        $urls = collect([
            ['loc' => url('/'), 'lastmod' => $this->site?->updated_at],
            ['loc' => url('/blog'), 'lastmod' => $this->site?->updated_at],
            ['loc' => url('/about'), 'lastmod' => $this->site?->updated_at],
            ['loc' => url('/contact'), 'lastmod' => $this->site?->updated_at],
        ]);

        foreach ($posts as $post) {
            $urls->push(['loc' => route('post.show', $post->slug), 'lastmod' => $post->updated_at]);
        }

        foreach ($pages as $page) {
            $urls->push(['loc' => route('page.show', $page->slug), 'lastmod' => $page->updated_at]);
        }

        foreach (Category::orderBy('name')->get(['slug', 'updated_at']) as $category) {
            $urls->push(['loc' => route('category.show', $category->slug), 'lastmod' => $category->updated_at]);
        }

        foreach (Tag::orderBy('name')->get(['slug', 'updated_at']) as $tag) {
            $urls->push(['loc' => route('tag.show', $tag->slug), 'lastmod' => $tag->updated_at]);
        }

        return response()
            ->view('themes._shared.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * RSS 2.0 feed of the latest published posts.
     */
    public function feed()
    {
        $posts = Post::query()
            ->published()
            ->where('post_type', 'post')
            ->orderByDesc('published_at')
            ->limit($this->settings->post_per_page ?: 10)
            ->get();

        return response()
            ->view('themes._shared.feed', [
                'posts' => $posts,
                'site' => $this->site,
                'settings' => $this->settings,
            ])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    /**
     * Plain-text robots.txt.
     */
    public function robots()
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /login',
            'Disallow: /search',
        ];

        if (($this->settings->enable_xml_sitemap ?? true)) {
            $lines[] = '';
            $lines[] = 'Sitemap: ' . url('/sitemap.xml');
        }

        return response(implode("\n", $lines) . "\n")
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /**
     * Render a theme view, falling back to the default theme when the active
     * theme does not provide it.
     */
    protected function render(string $view, array $data = [])
    {
        $candidates = [
            "themes.{$this->themeSlug}.{$view}",
            "themes.rosetta.{$view}",
        ];

        $resolved = null;
        foreach ($candidates as $candidate) {
            if (view()->exists($candidate)) {
                $resolved = $candidate;
                break;
            }
        }

        abort_if($resolved === null, 500, "Theme view [{$view}] is missing.");

        $base = [
            'site' => $this->site,
            'settings' => $this->settings,
            'theme_slug' => $this->themeSlug,
            'theme_preview' => $this->themePreview,
            'theme_options' => $this->themeOptions(),
            'headerMenu' => Menu::forLocation('header', $this->site),
            'footerMenu' => Menu::forLocation('footer', $this->site),
        ];

        if (!empty($data['post']) && app()->bound('wordpress.compat')) {
            app('wordpress.compat')->setCurrentPost($data['post']);
        }

        return view($resolved, array_merge($base, $data));
    }

    /**
     * Merge theme defaults with the saved theme options for this site.
     */
    protected function themeOptions(): array
    {
        $defaults = [];
        $themeJson = base_path("themes/{$this->themeSlug}/theme.json");

        if (File::exists($themeJson)) {
            $decoded = json_decode(File::get($themeJson), true);
            $defaults = $decoded['settings'] ?? [];
        }

        if (isset($defaults['background_color']) && !isset($defaults['bg_color'])) {
            $defaults['bg_color'] = $defaults['background_color'];
        }

        $saved = array_filter(
            $this->site?->theme_options ?? [],
            fn ($value) => $value !== null && $value !== ''
        );

        return array_merge($defaults, $saved);
    }

    /**
     * Build the SEO payload for a post or page.
     */
    /**
     * Canonical URL for a paginated listing.
     */
    protected function pagedUrl(string $path, $paginator): string
    {
        $page = (int) ($paginator->currentPage() ?? 1);

        return $page > 1 ? url($path) . '?page=' . $page : url($path);
    }

    /**
     * Determine whether the current request is an authorized staff preview.
     */
    protected function wantsPreview(): bool
    {
        return request()->boolean('preview')
            && auth()->check()
            && auth()->user()->isStaff();
    }

    protected function seoForPost(Post $post, bool $preview = false): array
    {
        $meta = $post->meta ?? [];

        $image = $meta['og_image'] ?? null;
        if ($image && !preg_match('#^(https?:)?//#i', $image)) {
            $image = asset('storage/' . ltrim($image, '/'));
        }
        $image = $image ?: $post->featured_image_url;

        return $this->seo([
            'title' => $meta['seo_title'] ?? $post->title,
            'description' => $meta['seo_description'] ?? \Illuminate\Support\Str::limit($post->plain_excerpt, 160),
            'keywords' => $meta['focus_keyword'] ?? $this->settings->seo_default_keywords ?? '',
            'canonical' => $meta['canonical_url'] ?? url(
                ($post->post_type === 'page' ? '/pages/' : '/posts/') . $post->slug
            ),
            'robots' => $preview ? 'noindex, nofollow' : ($meta['robots'] ?? 'index'),
            'image' => $image,
            'type' => $post->post_type === 'post' ? 'article' : 'website',
            'published_time' => $post->published_at?->toIso8601String(),
            'modified_time' => $post->updated_at?->toIso8601String(),
        ]);
    }

    /**
     * Normalise an SEO payload and apply the configured title suffix.
     */
    protected function seo(array $values = []): array
    {
        $title = $values['title'] ?? $this->settings->site_title ?? $this->site->name ?? 'Rosetta CMS';
        $suffix = trim((string) $this->settings->seo_title_suffix);

        if ($suffix !== '' && $title !== '' && !str_contains($title, $suffix)) {
            $title .= ' ' . $suffix;
        }

        $robots = $values['robots'] ?? 'index';
        $robots = match ($robots) {
            'noindex' => 'noindex, follow',
            'nofollow' => 'index, nofollow',
            'index' => 'index, follow',
            default => $robots,
        };

        return [
            'title' => $title,
            'description' => $values['description'] ?? $this->settings->site_description ?? '',
            'keywords' => $values['keywords'] ?? $this->settings->seo_default_keywords ?? '',
            'canonical' => $values['canonical'] ?? url()->current(),
            'robots' => $robots,
            'image' => $values['image'] ?? null,
            'type' => $values['type'] ?? 'website',
            'published_time' => $values['published_time'] ?? null,
            'modified_time' => $values['modified_time'] ?? null,
            'site_name' => $this->settings->site_title ?: ($this->site->name ?? 'Rosetta CMS'),
        ];
    }

    /**
     * Find a few posts related to the given post by shared tags or categories.
     */
    protected function relatedPosts(Post $post)
    {
        $tagIds = $post->tags()->pluck('tags.id');
        $categoryIds = $post->categories()->pluck('categories.id');

        if ($tagIds->isEmpty() && $categoryIds->isEmpty()) {
            return collect();
        }

        return Post::query()
            ->published()
            ->where('post_type', 'post')
            ->whereKeyNot($post->getKey())
            ->where(function ($builder) use ($tagIds, $categoryIds) {
                if ($tagIds->isNotEmpty()) {
                    $builder->whereHas('tags', fn ($q) => $q->whereIn('tags.id', $tagIds));
                }
                if ($categoryIds->isNotEmpty()) {
                    $builder->orWhereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds));
                }
            })
            ->limit(3)
            ->get();
    }
}
