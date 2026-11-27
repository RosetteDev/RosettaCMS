<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Redirect;

class RedirectMiddleware
{
    /**
     * Per-site 301/302 redirects.
     *
     * Runs after CurrentSite so the site is available. Only GET/HEAD requests
     * are redirected; everything else passes through untouched.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('install', 'install/*')) {
            return $next($request);
        }

        if (!$request->isMethod('get') && !$request->isMethod('head')) {
            return $next($request);
        }

        $site = app('CurrentSite');

        if (!$site) {
            return $next($request);
        }

        $path = trim($request->path(), '/');

        if ($path === '') {
            return $next($request);
        }

        $redirect = Redirect::forSite($site)->get($path);

        if (!$redirect) {
            return $next($request);
        }

        // Count the hit (best effort — never block the response on it).
        try {
            $redirect->hit_count++;
            $redirect->saveQuietly();
        } catch (\Throwable $e) {
            report($e);
        }

        $status = $redirect->type === '302' ? 302 : 301;

        return redirect()->to($redirect->to_url, $status);
    }
}
