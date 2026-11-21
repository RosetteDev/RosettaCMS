<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrimStringsWithLimits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Limit search input length to prevent abuse
        if ($request->has('search')) {
            $request->merge([
                'search' => mb_substr(trim((string) $request->input('search')), 0, 100)
            ]);
        }

        return $next($request);
    }
}
