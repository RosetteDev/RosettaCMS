<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Site;

class CurrentSite
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('install', 'install/*')) {
            return $next($request);
        }
        // Try to resolve the current site based on the domain
        $site = Site::where('domain', $request->getHost())->first();

        // If no site found for this domain, try to get the default site
        if (!$site) {
            $site = Site::first();
        }

        // Share the current site with the application
        app()->singleton('CurrentSite', function () use ($site) {
            return $site;
        });

        // Make the current site available globally
        app()->instance('CurrentSite', $site);

        return $next($request);
    }
}
