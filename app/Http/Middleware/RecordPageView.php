<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records a pageview for public GET requests.
 *
 * Skips admin routes, installer, assets, feeds/sitemaps/robots and logged-in
 * staff, and non-HTML responses. The visitor_hash is a salted hash of the
 * IP and user agent — never the raw IP.
 */
class RecordPageView
{
    public function handle(Request $request, \Closure $next): Response
    {
        $response = $next($request);

        try {
            $this->record($request, $response);
        } catch (\Throwable $e) {
            // Analytics must never break the page.
        }

        return $response;
    }

    protected function record(Request $request, Response $response): void
    {
        if (!$this->shouldRecord($request, $response)) {
            return;
        }

        $path = $request->path();

        $site = Site::first();

        $ip = $request->ip();
        $visitorHash = $ip
            ? hash('sha256', $ip . '|' . $request->userAgent() . '|' . ($site?->id ?? 'site'))
            : null;

        PageView::create([
            'site_id' => $site?->id,
            'path' => $path === '' ? '/' : $path,
            'title' => $request->route()?->getName() ?? null,
            'post_id' => $request->route('post')?->id ?? null,
            'referrer' => $this->normalizeReferrer($request->headers->get('referer')),
            'device' => $this->deviceType($request->userAgent()),
            'browser' => $this->browser($request->userAgent()),
            'visitor_hash' => $visitorHash,
            'created_at' => now(),
        ]);
    }

    protected function shouldRecord(Request $request, Response $response): bool
    {
        if ($request->method() !== 'GET') {
            return false;
        }

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $contentType = $response->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'text/html')) {
            return false;
        }

        $path = $request->path();

        // Skip admin, installer, login/register, API and static assets.
        $skip = ['admin', 'installer', 'login', 'register', 'logout', 'password', 'two-factor', 'api', 'up', 'favicon.ico', 'robots.txt', 'sitemap', 'feed', '_debugbar'];
        foreach ($skip as $segment) {
            if ($path === $segment || str_starts_with($path, $segment . '/')) {
                return false;
            }
        }

        if (str_contains($path, '.')) {
            return false; // static files
        }

        // Do not count staff's own browsing.
        if (Auth::check() && Auth::user()?->isStaff()) {
            return false;
        }

        // Skip known bots.
        $ua = strtolower($request->userAgent() ?? '');
        foreach (['bot', 'crawl', 'spider', 'slurp', 'facebookexternalhit', 'linkedinbot', 'twitterbot', 'googlebot', 'bingbot'] as $bot) {
            if (str_contains($ua, $bot)) {
                return false;
            }
        }

        return true;
    }

    protected function normalizeReferrer(?string $referer): ?string
    {
        if (!$referer) {
            return null;
        }

        $host = parse_url($referer, PHP_URL_HOST);
        if (!$host) {
            return null;
        }

        return $host;
    }

    protected function deviceType(?string $ua): ?string
    {
        if (!$ua) {
            return null;
        }

        $ua = strtolower($ua);

        if (preg_match('/(ipad|tablet|playbook|silk|kindle|(android(?!.*mobile)))/', $ua)) {
            return 'tablet';
        }

        if (preg_match('/(mobi|iphone|ipod|android|blackberry|iemobile|opera mini)/', $ua)) {
            return 'mobile';
        }

        return 'desktop';
    }

    protected function browser(?string $ua): ?string
    {
        if (!$ua) {
            return null;
        }

        $ua = strtolower($ua);

        $browsers = [
            'edg' => 'Edge',
            'opr' => 'Opera',
            'chrome' => 'Chrome',
            'firefox' => 'Firefox',
            'safari' => 'Safari',
            'curl' => 'curl',
            'wget' => 'wget',
        ];

        foreach ($browsers as $needle => $name) {
            if (str_contains($ua, $needle)) {
                return $name;
            }
        }

        return 'Other';
    }
}
