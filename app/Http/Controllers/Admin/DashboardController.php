<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\Post;
use App\Models\Media;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(Request $request)
    {
        $settings = SiteSetting::forSite();
        
        // Dashboard statistics
        $stats = [
            'total_posts' => Post::where('post_type', 'post')->count(),
            'total_pages' => Post::where('post_type', 'page')->count(),
            'total_media' => Media::count(),
            'total_sites' => Site::count(),
            'total_users' => User::count(),
            'published_posts' => Post::where('post_type', 'post')->where('status', 'published')->count(),
            'pending_posts' => Post::where('post_type', 'post')->where('status', 'pending')->count(),
            'total_comments' => \App\Models\Comment::count(),
            'pending_comments' => \App\Models\Comment::where('status', 'pending')->count(),
        ];

        // Recent posts
        $recentPosts = Post::where('post_type', 'post')
            ->with('user')
            ->latest()
            ->limit(5)
            ->get();

        // Recent media
        $recentMedia = Media::latest()->limit(5)->get();

        // Active widgets from user preferences or session
        $user = auth()->user();
        $defaultWidgets = [
            'stats_overview' => ['status' => 'enabled', 'priority' => 'high'],
            'quick_actions' => ['status' => 'enabled', 'priority' => 'high'],
        ];
        
        $savedWidgets = $user->dashboard_widgets ?? $request->session()->get('dashboard_widgets', []);
        $widgets = array_merge($defaultWidgets, $savedWidgets);

        return view('admin.dashboard', compact('settings', 'stats', 'recentPosts', 'recentMedia', 'widgets'));
    }

    /**
     * Update dashboard widget preferences.
     */
    public function updateWidgets(Request $request)
    {
        $validated = $request->validate([
            'widgets' => 'required|array',
            'widgets.*.status' => 'nullable|in:enabled,disabled',
            'widgets.*.priority' => 'nullable|in:high,normal,low',
        ]);

        $request->session()->put('dashboard_widgets', $validated['widgets']);

        return redirect()->back()->with('success', 'Dashboard widgets updated successfully.');
    }
}
