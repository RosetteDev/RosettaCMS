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
            'total_media' => \App\Models\Media::count(),
            'total_sites' => \App\Models\Site::count(),
            'total_users' => \App\Models\User::count(),
        ];

        // Get recent posts
        $recent_posts = \App\Models\Post::orderBy('updated_at', 'desc')->limit(5)->get();

        return view('themes.rosetta.dashboard', compact('stats', 'recent_posts'));
    }
}
