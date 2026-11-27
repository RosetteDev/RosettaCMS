<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageView;
use App\Models\Post;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AnalyticsController extends Controller
{
    /**
     * Display the analytics dashboard.
     */
    public function index(Request $request)
    {
        $days = (int) $request->query('days', 30);
        $days = in_array($days, [7, 30, 90]) ? $days : 30;

        $site = Site::first();
        $since = now()->subDays($days)->startOfDay();

        $base = PageView::where('created_at', '>=', $since);
        if ($site) {
            $base->where('site_id', $site->id);
        }

        $views = (clone $base)->get();

        $totalViews = $views->count();
        $uniqueVisitors = $views->pluck('visitor_hash')->filter()->unique()->count();

        // Daily pageviews (array of dates => count)
        $daily = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $daily[$day] = 0;
        }
        foreach ($views as $view) {
            $day = $view->created_at?->toDateString();
            if ($day && array_key_exists($day, $daily)) {
                $daily[$day]++;
            }
        }

        // Top posts (by post_id) and top paths
        $topPosts = $views->whereNotNull('post_id')
            ->groupBy('post_id')
            ->map(function ($group, $postId) {
                $post = Post::find($postId);
                return [
                    'post_id' => $postId,
                    'title' => $post?->title ?? '(deleted post)',
                    'slug' => $post?->slug,
                    'views' => $group->count(),
                ];
            })
            ->sortByDesc('views')
            ->take(10)
            ->values();

        $topPaths = $views->groupBy('path')
            ->map(fn ($group, $path) => ['path' => $path, 'views' => $group->count()])
            ->sortByDesc('views')
            ->take(10)
            ->values();

        $topReferrers = $views->whereNotNull('referrer')
            ->groupBy('referrer')
            ->map(fn ($group, $referrer) => ['referrer' => $referrer, 'views' => $group->count()])
            ->sortByDesc('views')
            ->take(10)
            ->values();

        $deviceBreakdown = $views->groupBy('device')
            ->map(fn ($group, $device) => ['device' => $device ?: 'unknown', 'views' => $group->count()])
            ->sortByDesc('views')
            ->values();

        $browserBreakdown = $views->groupBy('browser')
            ->map(fn ($group, $browser) => ['browser' => $browser ?: 'unknown', 'views' => $group->count()])
            ->sortByDesc('views')
            ->take(10)
            ->values();

        $maxDaily = max(1, max(array_values($daily) ?: [1]));

        return view('admin.analytics.index', [
            'days' => $days,
            'totalViews' => $totalViews,
            'uniqueVisitors' => $uniqueVisitors,
            'daily' => $daily,
            'maxDaily' => $maxDaily,
            'topPosts' => $topPosts,
            'topPaths' => $topPaths,
            'topReferrers' => $topReferrers,
            'deviceBreakdown' => $deviceBreakdown,
            'browserBreakdown' => $browserBreakdown,
        ]);
    }

    /**
     * Export the analytics table as CSV.
     */
    public function export(Request $request)
    {
        $days = (int) $request->query('days', 30);
        $days = in_array($days, [7, 30, 90]) ? $days : 30;

        $site = Site::first();
        $since = now()->subDays($days)->startOfDay();

        $base = PageView::where('created_at', '>=', $since)->orderBy('created_at', 'desc');
        if ($site) {
            $base->where('site_id', $site->id);
        }

        $views = $base->limit(10000)->get();

        $filename = 'analytics-' . now()->format('Y-m-d-His') . '.csv';

        $callback = function () use ($views) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['created_at', 'path', 'title', 'post', 'referrer', 'device', 'browser', 'visitor_hash']);

            foreach ($views as $view) {
                $post = $view->post_id ? Post::find($view->post_id) : null;
                fputcsv($handle, [
                    $view->created_at?->toDateTimeString(),
                    $view->path,
                    $view->title,
                    $post?->title,
                    $view->referrer,
                    $view->device,
                    $view->browser,
                    $view->visitor_hash,
                ]);
            }

            fclose($handle);
        };

        return Response::streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
