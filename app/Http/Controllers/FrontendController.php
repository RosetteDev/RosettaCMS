<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Language;
use App\Models\LanguageContent;
use App\Models\Menu;
use App\Models\Post;
use App\Models\Site;
use App\Models\SiteSetting;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class FrontendController extends Controller
{
    use CacheHelper;

    /**
     * Resolved sites, keyed by request host.
     *
     * Laravel reuses controller instances across requests within a single
     * process, so request context must never be cached in a plain property.
     *
     * @var array<string, Site|null>
     */
    protected array $sites = [];

    /**
     * Resolved settings, keyed by site id.
     *
     * @var array<int, SiteSetting>
     */
    protected array $settingsBySite = [];

    /**
     * Resolve the site for the current request.
     */
    protected function site(): ?Site
    {
        $host = request()->getHost();

        if (!array_key_exists($host, $this->sites)) {
            $this->sites[$host] = Site::where('domain', $host)->first() ?: Site::first();
        }

        return $this->sites[$host];
    }

    /**
     * Resolve the settings for the current site.
     */
    protected function settings(): SiteSetting
    {
        // Use settingsWithCache() from CacheHelper trait
        return $this->settingsWithCache();
    }

    /**
     * The theme requested by an admin for preview, if any.
     */
    protected function previewTheme(): ?string
    {
        $preview = (string) request()->query('theme', '');

        if ($preview !== ''
            && preg_match('/^[A-Za-z0-9_-]+$/', $preview)
            && auth()->check()
            && auth()->user()->hasRole('admin')
            && file_exists(public_path('themes/' . $preview . '/css/style.css'))) {
            return $preview;
        }

        return null;
    }

    /**
     * The theme slug for the current request.
     */
    protected function themeSlug(): string
    {
        return $this->previewTheme() ?? ($this->site()->theme ?? 'rosetta');
    }

    /**
     * Whether the current request is previewing an inactive theme.
     */
    protected function themePreview(): bool
    {
        return $this->previewTheme() !== null;
    }

    /**
     * Public homepage.
     */
    public function home()
    {
        $site = $this->site();
        // A designated static homepage takes over the front page.
        if ($site && $site->home_post_id) {
            $homepage = $site->homepage;

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

        $settings = $this->settings();
        $posts = $this->scoped(Post::query())
            ->published()
            ->where('post_type', 'post')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->limit($settings->post_per_page ?: 10)
            ->get();

        return $this->render('home', [
            'posts' => $posts,
            'seo' => $this->seo([
                'title' => $settings->site_title ?: ($site->name ?? 'Rosetta CMS'),
                'description' => $settings->site_description ?: ($site->description ?? ''),
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

        $post = $this->resolveBySlug('post', $slug, !$preview);

        abort_unless($post, 404);

        $commentsEnabled = !$this->settings()->disable_comments;

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
        $post = $this->resolveBySlug('post', $slug, true);

        abort_unless($post, 404);

        if ($this->settings()->disable_comments) {
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

        $requiresApproval = (bool) ($this->settings()->enable_comment_approval ?? true);

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
        $keywords = array_filter(array_map('trim', explode(',', (string) $this->settings()->spam_keywords)));

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

        $page = $this->resolveBySlug('page', $slug, !$preview);

        abort_unless($page, 404);

        // Determine if inline edit mode is enabled
        $inlineEdit = request()->input('inline_edit') || config('rosetta.inline_edit_enabled', false);

        return $this->render('page', [
            'page' => $page,
            'post' => $page,
            'relatedPosts' => collect(),
            'preview' => $preview,
            'seo' => $this->seoForPost($page, $preview),
            'inlineEdit' => $inlineEdit,
        ]);
    }

    /**
     * About page: prefer a CMS page, otherwise fall back to the theme's About.
     */
    public function about()
    {
        $settings = $this->settings();
        $page = $this->scoped(Post::query())->published()->where('post_type', 'page')
            ->whereIn('slug', ['about', 'about-us', 'about-our-cms'])
            ->first();

        return $this->render('about', [
            'page' => $page,
            'post' => $page,
            'seo' => $this->seo([
                'title' => $page?->title ?: 'About Us',
                'description' => $settings->site_description,
                'canonical' => url('/about'),
            ]),
        ]);
    }

    /**
     * Contact page: prefer a CMS page, otherwise fall back to the theme's Contact.
     */
    /**
     * Send a contact-form message to the site administrator.
     */
    public function submitContact(Request $request)
    {
        // Silently accept bots that fill the hidden honeypot field.
        if ($request->filled('website')) {
            return back()->with('status', 'Thanks! Your message has been sent.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $settings = $this->settings();
        $recipient = $settings->admin_email
            ?: $settings->contact_email
            ?: config('mail.from.address');

        if (!$recipient) {
            return back()->withInput()
                ->with('error', 'This site has not configured a contact address yet.');
        }

        $subject = trim(str_replace(["\r", "\n"], ' ', $validated['subject']));

        try {
            Mail::to($recipient)->send(new \App\Mail\ContactMessage(
                senderName: $validated['name'],
                senderEmail: $validated['email'],
                messageSubject: $subject,
                messageBody: $validated['message'],
                siteName: $settings->site_title ?? $this->site()->name ?? 'your website',
            ));
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()
                ->with('error', 'Sorry, your message could not be sent. Please try again later.');
        }

        return back()->with('status', 'Thanks! Your message has been sent.');
    }

    public function contact()
    {
        $settings = $this->settings();
        $page = $this->scoped(Post::query())->published()->where('post_type', 'page')
            ->whereIn('slug', ['contact', 'contact-us'])
            ->first();

        return $this->render('contact', [
            'page' => $page,
            'post' => $page,
            'seo' => $this->seo([
                'title' => $page?->title ?: 'Contact Us',
                'description' => $settings->site_description,
                'canonical' => url('/contact'),
            ]),
        ]);
    }

    /**
     * Category archive.
     */
    public function category(string $slug)
    {
        $settings = $this->settings();
        $category = $this->scoped(Category::query())->where('slug', $slug)->firstOrFail();

        $posts = $this->scoped($category->posts())
            ->published()
            ->where('post_type', 'post')
            ->orderByDesc('published_at')
            ->with(['author', 'categories', 'tags'])
            ->paginate($settings->post_per_page ?: 10);

        return $this->render('category', [
            'category' => $category,
            'posts' => $posts,
            'seo' => $this->seo([
                'title' => 'Category: ' . $category->name,
                'description' => $category->description ?: $settings->site_description,
                'canonical' => $this->pagedUrl('/categories/' . $category->slug, $posts),
            ]),
        ]);
    }

    /**
     * Tag archive.
     */
    public function tag(string $slug)
    {
        $settings = $this->settings();
        $tag = $this->scoped(Tag::query())->where('slug', $slug)->firstOrFail();

        $posts = $this->scoped($tag->posts())
            ->published()
            ->where('post_type', 'post')
            ->orderByDesc('published_at')
            ->with(['author', 'categories', 'tags'])
            ->paginate($settings->post_per_page ?: 10);

        return $this->render('tag', [
            'tag' => $tag,
            'posts' => $posts,
            'seo' => $this->seo([
                'title' => 'Tag: ' . $tag->name,
                'description' => $tag->description ?: $settings->site_description,
                'canonical' => $this->pagedUrl('/tags/' . $tag->slug, $posts),
            ]),
        ]);
    }

    /**
     * Blog archive.
     */
    public function blog()
    {
        $settings = $this->settings();
        $posts = $this->scoped(Post::query())
            ->published()
            ->where('post_type', 'post')
            ->orderByDesc('published_at')
            ->with(['author', 'categories', 'tags'])
            ->paginate($settings->post_per_page ?: 10);

        return $this->render('category', [
            'category' => null,
            'posts' => $posts,
            'seo' => $this->seo([
                'title' => 'Blog',
                'description' => $settings->site_description,
                'canonical' => $this->pagedUrl('/blog', $posts),
            ]),
        ]);
    }

    /**
     * Search results.
     */
    public function search(Request $request)
    {
        $settings = $this->settings();
        $query = trim((string) $request->input('q'));
        
        // Limit search query length to prevent abuse
        $query = mb_substr($query, 0, 100);

        $posts = $this->scoped(Post::query())
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
            ->with(['author', 'categories', 'tags'])
            ->paginate($settings->post_per_page ?: 10);

        return $this->render('search', [
            'query' => $query,
            'posts' => $posts,
            'seo' => $this->seo([
                'title' => $query !== '' ? "Search: {$query}" : 'Search',
                'description' => $settings->site_description,
                'canonical' => url('/search'),
                'robots' => 'noindex',
            ]),
        ]);
    }

    /**
     * Sitemap index.
     */
    public function sitemap()
    {
        $settings = $this->settings();
        abort_unless((bool) ($settings->enable_xml_sitemap ?? true), 404);

        $sitemap = [
            'posts' => url('/sitemap-posts.xml'),
            'pages' => url('/sitemap-pages.xml'),
            'categories' => url('/sitemap-categories.xml'),
            'tags' => url('/sitemap-tags.xml'),
        ];

        return response()
            ->view('themes._shared.sitemap-index', ['sitemaps' => $sitemap])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Sitemap of published posts (with per-language alternates and images).
     */
    public function sitemapPosts()
    {
        return $this->sitemapContent('post');
    }

    /**
     * Sitemap of published pages.
     */
    public function sitemapPages()
    {
        return $this->sitemapContent('page');
    }

    /**
     * Sitemap of categories.
     */
    public function sitemapCategories()
    {
        $settings = $this->settings();
        abort_unless((bool) ($settings->enable_xml_sitemap ?? true), 404);

        $urls = collect();

        foreach ($this->scoped(Category::query())->orderBy('name')->get() as $category) {
            $urls->push([
                'loc' => route('category.show', $category->slug),
                'lastmod' => $category->updated_at,
            ]);
        }

        return $this->sitemapResponse($urls);
    }

    /**
     * Sitemap of tags.
     */
    public function sitemapTags()
    {
        $settings = $this->settings();
        abort_unless((bool) ($settings->enable_xml_sitemap ?? true), 404);

        $urls = collect();

        foreach ($this->scoped(Tag::query())->orderBy('name')->get() as $tag) {
            $urls->push([
                'loc' => route('tag.show', $tag->slug),
                'lastmod' => $tag->updated_at,
            ]);
        }

        return $this->sitemapResponse($urls);
    }

    /**
     * Sitemap of posts or pages, honoring the per-language alternates and
     * excluding drafts and noindex content.
     */
    protected function sitemapContent(string $postType)
    {
        $settings = $this->settings();
        abort_unless((bool) ($settings->enable_xml_sitemap ?? true), 404);

        $urls = collect();

        $posts = $this->scoped(Post::query())
            ->published()
            ->where('post_type', $postType)
            ->orderByDesc('updated_at')
            ->get();

        foreach ($posts as $post) {
            // Skip noindex content.
            if ($this->isNoindex($post)) {
                continue;
            }

            $url = [
                'loc' => $this->canonicalUrlFor($post),
                'lastmod' => $post->updated_at,
                'alternates' => $this->hreflangAlternates($post),
            ];

            if (!empty($post->featured_image)) {
                $url['images'] = [['url' => $post->featured_image_url]];
            }

            $urls->push($url);
        }

        return $this->sitemapResponse($urls);
    }

    /**
     * Whether the post carries a noindex robots directive.
     */
    protected function isNoindex(Post $post): bool
    {
        $meta = $post->meta;

        if (!is_array($meta)) {
            return false;
        }

        $robots = $meta['robots'] ?? '';

        return is_string($robots) && str_contains($robots, 'noindex');
    }

    /**
     * Render a urlset response.
     */
    protected function sitemapResponse($urls)
    {
        return response()
            ->view('themes._shared.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * RSS 2.0 feed of the latest published posts.
     */
    public function feed()
    {
        $settings = $this->settings();
        $site = $this->site();

        $posts = $this->scoped(Post::query())
            ->published()
            ->where('post_type', 'post')
            ->orderByDesc('published_at')
            ->limit($settings->post_per_page ?: 10)
            ->get();

        return response()
            ->view('themes._shared.feed', [
                'posts' => $posts,
                'site' => $site,
                'settings' => $settings,
            ])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    /**
     * Plain-text robots.txt.
     */
    public function robots()
    {
        $settings = $this->settings();

        // Default disallows always apply; per-site rules are appended on top.
        $disallow = array_merge(
            ['/admin', '/login', '/search'],
            $settings->robotsLines('robots_disallow')
        );

        $allow = $settings->robotsLines('robots_allow');

        $lines = ['User-agent: *'];

        foreach ($allow as $path) {
            $lines[] = 'Allow: ' . $path;
        }

        foreach ($disallow as $path) {
            $lines[] = 'Disallow: ' . $path;
        }

        if (($settings->enable_xml_sitemap ?? true)) {
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
    /**
     * Apply the active language's translation to a post/page in memory.
     *
     * When the site default language is active, or no translation exists,
     * the post is returned unchanged. The model instance is mutated only
     * for the duration of the request — the database is never touched.
     */
    protected function localizePost(?Post $post): ?Post
    {
        if (!$post instanceof Post) {
            return $post;
        }

        $language = request()->attributes->get('activeLanguage');

        if (!$language) {
            return $post;
        }

        $default = Language::defaultLanguageFor($this->site());

        if (!$default || $language->code === $default->code) {
            return $post;
        }

        $translation = LanguageContent::where('content_type', $post->post_type)
            ->where('content_id', $post->id)
            ->where('language_id', $language->id)
            ->first();

        if (!$translation) {
            return $post;
        }

        if ($translation->title) {
            $post->title = $translation->title;
        }

        if ($translation->content !== null) {
            $post->content = $translation->content;
        }

        if ($translation->excerpt !== null) {
            $post->excerpt = $translation->excerpt;
        }

        // Apply the translated slug when one is stored — the URL rendered
        // from this model (and its public_url / menu links) then points at
        // the translated slug. The route resolver matches both the default
        // and the translated slug, so both keep resolving.
        if ($translation->slug) {
            $post->slug = $translation->slug;
        }

        return $post;
    }

    /**
     * Localize any post-like values inside a render payload.
     *
     * localizePost() mutates the model in place, so this only walks the
     * payload and touches any Post instances it finds (direct values,
     * collections, paginators or nested arrays).
     */
    protected function localizeData(array $data): array
    {
        foreach ($data as $key => $value) {
            $this->localizeValue($value);
        }

        return $data;
    }

    /**
     * @param  mixed  $value
     */
    protected function localizeValue($value): void
    {
        if ($value instanceof Post) {
            $this->localizePost($value);
            return;
        }

        if ($value instanceof \Illuminate\Contracts\Pagination\Paginator) {
            foreach ($value->items() as $item) {
                $this->localizeValue($item);
            }
            return;
        }

        if ($value instanceof \Illuminate\Support\Collection) {
            foreach ($value as $item) {
                $this->localizeValue($item);
            }
            return;
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                $this->localizeValue($item);
            }
        }
    }

    protected function render(string $view, array $data = [])
    {
        $data = $this->localizeData($data);
        $themeSlug = $this->themeSlug();
        $candidates = [
            "themes.{$themeSlug}.{$view}",
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

        $site = $this->site();
        $settings = $this->settings();

        $base = [
            'site' => $site,
            'settings' => $settings,
            'theme_slug' => $themeSlug,
            'theme_preview' => $this->themePreview(),
            'theme_options' => $this->themeOptions(),
            'headerMenu' => Menu::forLocation('header', $site),
            'footerMenu' => Menu::forLocation('footer', $site),
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
        $themeSlug = $this->themeSlug();
        $defaults = [];
        $themeJson = base_path("themes/{$themeSlug}/theme.json");

        if (File::exists($themeJson)) {
            $decoded = json_decode(File::get($themeJson), true);
            $defaults = $decoded['settings'] ?? [];
        }

        if (isset($defaults['background_color']) && !isset($defaults['bg_color'])) {
            $defaults['bg_color'] = $defaults['background_color'];
        }

        $site = $this->site();
        $saved = array_filter(
            $site?->theme_options ?? [],
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
     * Resolve a post/page by slug, trying the translated slug for the active
     * language when the default slug does not match.
     *
     * The default slug is always honoured (so existing URLs keep resolving);
     * a translated slug is only used as a fallback when the active language
     * differs from the site's default language.
     */
    protected function resolveBySlug(string $type, string $slug, bool $published)
    {
        $query = $this->scoped(Post::query())
            ->where('post_type', $type)
            ->where('slug', $slug);

        if ($published) {
            $query->published();
        }

        $post = $query->first();

        if ($post) {
            return $post;
        }

        // Fall back to a translated slug for the active language.
        $language = request()->attributes->get('activeLanguage');

        if (!$language) {
            return null;
        }

        $default = Language::defaultLanguageFor($this->site());

        if (!$default || $language->code === $default->code) {
            return null;
        }

        $translation = LanguageContent::where('content_type', $type)
            ->where('language_id', $language->id)
            ->where('slug', $slug)
            ->first();

        if (!$translation) {
            return null;
        }

        $postQuery = $this->scoped(Post::query())
            ->where('post_type', $type)
            ->where('id', $translation->content_id);

        if ($published) {
            $postQuery->published();
        }

        return $postQuery->first();
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

        $settings = $this->settings();

        return $this->seo([
            'title' => $meta['seo_title'] ?? $post->title,
            'description' => $meta['seo_description'] ?? \Illuminate\Support\Str::limit($post->plain_excerpt, 160),
            'keywords' => $meta['focus_keyword'] ?? $settings->seo_default_keywords ?? '',
            'canonical' => $meta['canonical_url'] ?? $this->canonicalUrlFor($post),
            'robots' => $preview ? 'noindex, nofollow' : ($meta['robots'] ?? 'index'),
            'image' => $image,
            'type' => $post->post_type === 'post' ? 'article' : 'website',
            'published_time' => $post->published_at?->toIso8601String(),
            'modified_time' => $post->updated_at?->toIso8601String(),
            'post' => $post,
            'faq' => $meta['faq'] ?? null,
        ]);
    }

    /**
     * Normalise an SEO payload and apply the configured title suffix.
     */
    protected function seo(array $values = []): array
    {
        $settings = $this->settings();
        $site = $this->site();

        $siteName = $settings->site_title ?: ($site->name ?? 'Rosetta CMS');
        $title = $values['title'] ?? $siteName;
        $title = $settings->titleTemplate($title, $siteName);

        $robots = $values['robots'] ?? 'index';
        $robots = match ($robots) {
            'noindex' => 'noindex, follow',
            'nofollow' => 'index, nofollow',
            'index' => 'index, follow',
            default => $robots,
        };

        return [
            'title' => $title,
            'description' => $values['description'] ?? $settings->site_description ?? '',
            'keywords' => $values['keywords'] ?? $settings->seo_default_keywords ?? '',
            'canonical' => $values['canonical'] ?? url()->current(),
            'robots' => $robots,
            'image' => $values['image'] ?? $settings->defaultOgImage(),
            'type' => $values['type'] ?? 'website',
            'published_time' => $values['published_time'] ?? null,
            'modified_time' => $values['modified_time'] ?? null,
            'site_name' => $siteName,
            'twitter_handle' => $settings->twitter_handle ?: null,
            'verification_meta' => $settings->verificationMetas(),
            'og_locale' => $this->ogLocale(),
            'hreflang' => $values['hreflang'] ?? $this->hreflangAlternates($values['post'] ?? null),
            'faq' => $values['faq'] ?? null,
            'post' => $values['post'] ?? null,
        ];
    }

    /**
     * The og:locale for the active language (e.g. "en_US").
     */
    protected function ogLocale(): string
    {
        $language = request()->attributes->get('activeLanguage');

        if ($language?->code) {
            return str_replace('-', '_', $language->code);
        }

        return str_replace('-', '_', app()->getLocale());
    }

    /**
     * Hreflang alternates for the given post/page (or the current page).
     * Returns [language_code => url].
     */
    protected function hreflangAlternates(?Post $post = null): array
    {
        $languages = Language::where('is_active', true)->orderBy('sort_order')->get();

        if ($languages->count() < 2) {
            return [];
        }

        $alternates = [];

        foreach ($languages as $language) {
            $url = $this->urlForLanguage($language, $post);

            if ($url) {
                $alternates[$language->code] = $url;
            }
        }

        return $alternates;
    }

    /**
     * Resolve the URL of the given post/page in the given language.
     *
     * Returns null when the content does not exist in that language —
     * hreflang links must only point at pages that actually exist.
     */
    protected function urlForLanguage(Language $language, ?Post $post = null): ?string
    {
        $default = Language::defaultLanguageFor($this->site());

        if (!$default) {
            return null;
        }

        if ($language->code === $default->code) {
            return $post ? $this->canonicalUrlFor($post) : url()->current();
        }

        if (!$post instanceof Post) {
            // Non-post pages have no per-language URL yet.
            return null;
        }

        $translation = LanguageContent::where('content_type', $post->post_type)
            ->where('content_id', $post->id)
            ->where('language_id', $language->id)
            ->first();

        if (!$translation) {
            return null;
        }

        $slug = $translation->slug ?: $post->slug;

        return $this->canonicalUrlFor($post, $slug);
    }

    /**
     * Canonical URL for a post/page (default language), optionally with a
     * translated slug.
     */
    protected function canonicalUrlFor(Post $post, ?string $slug = null): string
    {
        $slug = $slug ?: $post->slug;

        return url(($post->post_type === 'page' ? '/pages/' : '/posts/') . $slug);
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

        return $this->scoped(Post::query())
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
