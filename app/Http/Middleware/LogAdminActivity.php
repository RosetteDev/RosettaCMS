<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ActivityLog;

class LogAdminActivity
{
    /**
     * Mutating HTTP verbs that count as an admin action worth recording.
     */
    protected const MUTATING = ['POST', 'PUT', 'PATCH', 'DELETE'];

    /**
     * Record mutating admin requests in the activity log.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!in_array($request->method(), self::MUTATING, true)) {
            return $response;
        }

        // Only log successful actions — a failed request is not an admin action.
        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        try {
            $route = $request->route();
            $routeName = $route?->getName();
            $user = $request->user();

            ActivityLog::record(
                $routeName ?: $request->method() . ' ' . $request->path(),
                $routeName ? 'Admin action: ' . $routeName : $request->method() . ' ' . $request->path(),
                null,
                null,
                $user?->id,
            );
        } catch (\Throwable $e) {
            // Never let audit logging break the response.
            report($e);
        }

        return $response;
    }
}
