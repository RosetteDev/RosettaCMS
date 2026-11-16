<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index()
    {
        // Calculate dashboard stats
        $stats = [
            'total_posts' => \App\Models\Post::count(),
            'total_pages' => \App\Models\Post::where('post_type', 'page')->count(),
            'total_media' => \App\Models\Media::count(),
            'total_sites' => \App\Models\Site::count(),
            'total_users' => \App\Models\User::count(),
            'total_comments' => \App\Models\Comment::count(),
            'pending_comments' => \App\Models\Comment::where('status', 'pending')->count(),
            'approved_comments' => \App\Models\Comment::where('status', 'approved')->count(),
            'spam_comments' => \App\Models\Comment::where('status', 'spam')->count(),
        ];

        // Get recent posts
        $recent_posts = \App\Models\Post::orderBy('updated_at', 'desc')->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'recent_posts'));
    }
}
