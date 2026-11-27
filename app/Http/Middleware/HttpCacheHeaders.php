<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Sets long-lived cache headers on static assets and moderate caching on
 * public, anonymous HTML responses. Hashed Vite assets are immutable;
 * storage files get a day of caching (they can be replaced in place).
 *
 * Authenticated pages (admin, dashboard, profile) are left untouched so
 * they are never cached by shared proxies.
 */
class HttpCacheHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $path = $request->path();

        // Static assets — safe for anonymous browsers.
        if (str_starts_with($path, 'build/')) {
            // Fingerprinted build assets — safe to cache forever.
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
            return $response;
        }

        if (str_starts_with($path, 'storage/')) {
            // User uploads may be replaced — cache for a day.
            $response->headers->set('Cache-Control', 'public, max-age=86400');
            return $response;
        }

        // Authenticated or API requests: never cache publicly.
        if ($request->user() || $request->is('api/*') || $request->is('wp-json/*')) {
            return $response;
        }

        if (! $response->headers->has('Cache-Control')) {
            $response->headers->set('Cache-Control', 'public, max-age=300');
        }

        return $response;
    }
}
