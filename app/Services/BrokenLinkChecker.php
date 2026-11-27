<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;

/**
 * Scans published posts and pages for internal links and reports which of
 * them point at resources that no longer exist.
 */
class BrokenLinkChecker
{
    /**
     * Paths that always resolve (static routes and utility endpoints).
     */
    protected const KNOWN_PATHS = [
        '/', '/blog', '/about', '/contact', '/search', '/feed',
        '/sitemap.xml', '/robots.txt', '/up',
    ];

    /**
     * Run the check. Returns ['broken' => [...], 'checked' => int, 'found' => int].
     */
    public function check(?int $siteId = null): array
    {
        $query = Post::query()->published();

        if ($siteId) {
            $query->where('site_id', $siteId);
        }

        $posts = $query->get();

        $found = 0;
        $broken = [];

        foreach ($posts as $post) {
            foreach ($this->internalLinks($post) as $url) {
                $found++;

                if (!$this->isResolvable($url, $siteId)) {
                    $broken[] = [
                        'post_id' => $post->id,
                        'post_title' => $post->title,
                        'post_url' => $post->public_url,
                        'url' => $url,
                    ];
                }
            }
        }

        return ['broken' => $broken, 'found' => $found, 'checked' => $posts->count()];
    }

    /**
     * Extract internal href targets from a post's rendered content.
     *
     * @return array<int, string>
     */
    protected function internalLinks(Post $post): array
    {
        $html = $post->rendered_content;

        if (!$html) {
            return [];
        }

        preg_match_all('/<a[^>]+href=["\']([^"\']+)["\']/i', $html, $matches);

        $links = [];

        foreach ($matches[1] as $href) {
            $href = trim($href);

            if ($href === '') {
                continue;
            }

            // Skip external, absolute-URL and fragment links.
            if (preg_match('#^(?:https?:)?//#i', $href)) {
                continue;
            }

            if (preg_match('~^(mailto:|tel:|javascript:|#|data:)~i', $href)) {
                continue;
            }

            // Strip fragments and query strings.
            $href = strtok($href, '#?');

            if ($href === false || $href === '') {
                continue;
            }

            if (!str_starts_with($href, '/')) {
                continue;
            }

            // Normalize: strip trailing slash (except root).
            $href = $href === '/' ? '/' : rtrim($href, '/');

            $links[] = $href;
        }

        return array_values(array_unique($links));
    }

    /**
     * Whether a normalized internal path resolves to something real.
     */
    protected function isResolvable(string $path, ?int $siteId): bool
    {
        if (in_array($path, self::KNOWN_PATHS, true)) {
            return true;
        }

        // /posts/{slug} or /pages/{slug}
        if (preg_match('#^/(posts|pages)/([^/]+)$#', $path, $m)) {
            $postType = $m[1] === 'posts' ? 'post' : 'page';
            $slug = $m[2];

            $query = Post::query()
                ->published()
                ->where('post_type', $postType)
                ->where('slug', $slug);

            if ($siteId) {
                $query->where('site_id', $siteId);
            }

            return $query->exists();
        }

        // /categories/{slug}
        if (preg_match('#^/categories/([^/]+)$#', $path, $m)) {
            $query = Category::query()->where('slug', $m[1]);

            if ($siteId) {
                $query->where('site_id', $siteId);
            }

            return $query->exists();
        }

        // /tags/{slug}
        if (preg_match('#^/tags/([^/]+)$#', $path, $m)) {
            $query = Tag::query()->where('slug', $m[1]);

            if ($siteId) {
                $query->where('site_id', $siteId);
            }

            return $query->exists();
        }

        // Any other path: treat as unknown (don't flag) — it may be a plugin
        // route or an asset we cannot see here.
        return true;
    }
}
