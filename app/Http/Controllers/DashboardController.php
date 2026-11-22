<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index(Request $request)
    {
        // Subscribers have no access to the administration area.
        if (!$request->user()->isStaff()) {
            return redirect()->route('profile');
        }

        // Get site settings
        $settings = SiteSetting::forSite();
        
        // Calculate dashboard stats
        $stats = [
            'total_posts' => \App\Models\Post::where('post_type', 'post')->count(),
            'total_pages' => \App\Models\Post::where('post_type', 'page')->count(),
            'total_media' => \App\Models\Media::count(),
            'total_sites' => \App\Models\Site::count(),
            'total_users' => \App\Models\User::count(),
            'active_users' => \App\Models\User::whereNotNull('email_verified_at')->count(),
            'pending_users' => \App\Models\User::whereNull('email_verified_at')->count(),
            'total_comments' => \App\Models\Comment::count(),
            'pending_comments' => \App\Models\Comment::where('status', 'pending')->count(),
            'approved_comments' => \App\Models\Comment::where('status', 'approved')->count(),
            'spam_comments' => \App\Models\Comment::where('status', 'spam')->count(),
        ];

        // Get recent posts
        $recentPosts = \App\Models\Post::orderBy('updated_at', 'desc')->limit(5)->get();

        // Get recent media
        $recentMedia = \App\Models\Media::orderBy('created_at', 'desc')->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'recentPosts', 'recentMedia', 'settings'));
    }
}
