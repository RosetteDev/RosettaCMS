<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;

class InlineEditMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Only allow users with inline_edit capability
        if (!Auth::check() || !Auth::user()->can('inline_edit')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Set mode for the view
        $request->merge(['inline_edit' => true]);

        return $next($request);
    }
}
