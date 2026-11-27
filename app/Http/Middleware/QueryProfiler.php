<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Collects database queries for admin requests so the dashboard can show
 * a query-profiler card (total queries, slowest queries).
 */
class QueryProfiler
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->is('admin/*') && ! $request->is('dashboard')) {
            return $next($request);
        }

        DB::enableQueryLog();

        try {
            $response = $next($request);
        } finally {
            $log = DB::getQueryLog();
            DB::disableQueryLog();
        }

        $queries = array_map(function ($q) {
            return [
                'sql' => $q['query'],
                'time_ms' => round($q['time'], 2),
            ];
        }, $log);

        $request->attributes->set('queries', $queries);

        return $response;
    }
}
