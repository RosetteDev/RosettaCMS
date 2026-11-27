<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\IpMatchService;

class CheckSiteIpRules
{
    /**
     * Paths that must stay reachable even when the site has IP rules in place
     * (login must never lock the legitimate admin out; the health check must stay up).
     *
     * The global middleware runs before the route is matched, so matching by
     * route name is not possible here — match by path instead.
     */
    protected array $exemptPaths = [
        'login', 'login/2fa',
        'register',
        'forgot-password', 'reset-password',
        'logout',
        'up',
    ];

    public function __construct(protected IpMatchService $ipMatch)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        // Install wizard must stay reachable on a fresh site.
        if ($request->is('install', 'install/*')) {
            return $next($request);
        }

        $path = trim($request->path(), '/');

        if (in_array($path, $this->exemptPaths, true)) {
            return $next($request);
        }

        // Also exempt reset-password tokens (path contains the token).
        if (str_starts_with($path, 'reset-password/')) {
            return $next($request);
        }

        $site = app('CurrentSite');

        if (!$site) {
            return $next($request);
        }

        $rules = $site->ipRules()->get();

        if ($rules->isEmpty()) {
            return $next($request);
        }

        $ip = $request->ip();

        foreach ($rules->where('type', 'block') as $rule) {
            if ($this->matches($ip, $rule->cidr)) {
                return $this->forbidden();
            }
        }

        $allowRules = $rules->where('type', 'allow');

        if ($allowRules->isNotEmpty()) {
            foreach ($allowRules as $rule) {
                if ($this->matches($ip, $rule->cidr)) {
                    return $next($request);
                }
            }

            return $this->forbidden();
        }

        return $next($request);
    }

    protected function matches(string $ip, string $cidr): bool
    {
        try {
            return $this->ipMatch->matches($ip, $cidr);
        } catch (\Throwable $e) {
            // A malformed stored rule must not block the site — log and skip.
            report($e);
            return false;
        }
    }

    protected function forbidden()
    {
        return response('Access denied.', 403)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }
}
