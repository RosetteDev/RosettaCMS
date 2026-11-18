<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CheckMaintenanceMode
{
    /**
     * Show a maintenance page to visitors while the setting is enabled.
     * Administrators and editors keep full access.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $settings = SiteSetting::forSite();
        } catch (Throwable $e) {
            return $next($request);
        }

        if (!$settings || !$settings->maintenance_mode) {
            return $next($request);
        }

        // Login, the admin area and health checks stay reachable.
        if ($request->is('admin', 'admin/*', 'login', 'logout', 'up')) {
            return $next($request);
        }

        $user = $request->user();

        if ($user && $user->roles()->whereIn('slug', ['admin', 'editor'])->exists()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'The site is down for maintenance.'], 503);
        }

        return response()->view('errors.maintenance', [
            'siteName' => $settings->site_title ?: config('app.name'),
        ], 503);
    }
}
