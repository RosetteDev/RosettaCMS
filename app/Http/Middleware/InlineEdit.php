<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InlineEdit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Only process for GET requests
        if ($request->getMethod() !== 'GET') {
            return $next($request);
        }

        // Only for authenticated users with edit permissions
        if (!Auth::check() || !Auth::user()->can('edit_posts')) {
            return $next($request);
        }

        // Check if inline editing is enabled (via query parameter or config)
        if (!$request->input('inline_edit') && !config('rosetta.inline_edit_enabled', false)) {
            return $next($request);
        }

        // Check if the user is an admin
        if (!Auth::user()->hasRole('admin')) {
            return $next($request);
        }

        // Add inline edit data to the view
       view()->composer('*', function ($view) {
            $view->with('inlineEdit', true);
        });

        return $next($request);
    }
}
