<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Full-page cache for public, anonymous content pages.
 *
 * Only whitelisted GET routes are cached, keyed by URL + content version +
 * active language, and invalidated by bumping the content_version cache key
 * whenever content is saved. Pages with forms or per-visitor state (cart,
 * comments, contact) are not whitelisted.
 *
 * The web stack starts a session for every web request, which makes the
 * response set cookies. Those cookies are stripped from the cached copy and
 * from cache hits — anonymous visitors of pure-content pages do not need a
 * session, and replaying one visitor's session cookie to another visitor
 * would leak session state. A missed request is served with its cookies
 * intact.
 */
class FullPageCache
{
    /**
     * Content-only public routes safe to cache.
     */
    protected const ROUTES = [
        'home',
        'about',
        'blog',
        'page.show',
        'category.show',
        'tag.show',
        'sitemap',
        'sitemap.posts',
        'sitemap.pages',
        'sitemap.categories',
        'sitemap.tags',
        'feed',
        'robots',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        if ($request->user()) {
            return $next($request);
        }

        // Resolve the route explicitly: routing has not happened yet at
        // global-middleware time, so $request->route() is null.
        try {
            $route = app('router')->getRoutes()->match($request);
            $name = $route->getName();
        } catch (\Throwable $e) {
            return $next($request);
        }

        if (! in_array($name, self::ROUTES, true)) {
            return $next($request);
        }

        // Fold the active language into the key so translated content
        // never leaks across visitors.
        $version = (int) \Cache::get('content_version', 0);
        $language = session('active_language', 'en');
        $key = 'full_page.' . $version . '.' . md5($request->fullUrl() . '|' . $language);

        $cached = \Cache::get($key);
        if ($cached) {
            $response = new Response($cached['body'], $cached['status'], $cached['headers']);
            $response->headers->set('X-Full-Page-Cache', 'HIT');
            $response->headers->set('Cache-Control', 'public, max-age=300');
            return $response;
        }

        $response = $next($request);

        // Only cache successful responses that are safe to reuse.
        if (! $response instanceof Response || $response->getStatusCode() !== 200) {
            return $response;
        }

        if ($response->headers->has('Cache-Control') && str_contains($response->headers->get('Cache-Control'), 'no-store')) {
            return $response;
        }

        // Strip per-visitor cookies from the cached copy; they are never
        // replayed to other visitors.
        $headers = $response->headers->all();
        unset($headers['set-cookie']);

        \Cache::put($key, [
            'body' => $response->getContent(),
            'status' => $response->getStatusCode(),
            'headers' => $headers,
        ], now()->addMinutes(5));

        $response->headers->set('X-Full-Page-Cache', 'MISS');
        $response->headers->set('Cache-Control', 'public, max-age=300');

        return $response;
    }
}
