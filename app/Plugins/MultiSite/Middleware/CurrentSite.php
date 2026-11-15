<?php

namespace RosettaCMS\Plugins\MultiSite\Middleware;

use Closure;
use RosettaCMS\Models\Site;
use RosettaCMS\Plugins\MultiSite\MultiSiteManager;

class CurrentSite
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $manager = app(MultiSiteManager::class);
        
        // Set the current site
        $site = $manager->current();
        
        if ($site) {
            // Set the site in the request
            $request->setSite($site);
            
            // Set app locale based on site
            app()->setLocale($site->locale ?? 'en');
            
            // Set app timezone based on site
            config(['app.timezone' => $site->timezone ?? 'UTC']);
        }
        
        return $next($request);
    }
}
