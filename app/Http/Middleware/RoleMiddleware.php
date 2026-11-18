<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Request): (\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized action.');
        }

        // Process all roles, handling both single roles and pipe-separated roles
        $requiredRoles = [];
        foreach ($roles as $role) {
            // Handle pipe-separated roles (e.g., "admin|editor")
            $individualRoles = array_map('trim', explode('|', $role));
            $requiredRoles = array_merge($requiredRoles, $individualRoles);
        }

        // Get user's role slugs using exists() to avoid loading relation
        $userHasRole = false;
        foreach ($requiredRoles as $role) {
            if (!empty($role) && $user->roles()->where('slug', $role)->exists()) {
                $userHasRole = true;
                break;
            }
        }

        if (!$userHasRole) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
