<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Admin global search and notifications center.
 *
 * Notifications are derived from live data (pending comments/posts,
 * scheduled posts, recent activity) rather than stored records, so they
 * never go stale.
 */
class SearchController extends Controller
{
    /**
     * JSON search across posts, pages, media and users.
     */
    public function search(Request $request)
    {
        $q = trim((string) $request->input('q'));

        if ($q === '' || mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $limit = 5;

        $posts = Post::where('post_type', 'post')
            ->where('title', 'like', "%{$q}%")
            ->limit($limit)
            ->get()
            ->map(fn ($p) => [
                'type' => 'Post',
                'label' => $p->title,
                'url' => route('admin.posts.edit', $p->id),
            ]);

        $pages = Post::where('post_type', 'page')
            ->where('title', 'like', "%{$q}%")
            ->limit($limit)
            ->get()
            ->map(fn ($p) => [
                'type' => 'Page',
                'label' => $p->title,
                'url' => route('admin.pages.edit', $p->id),
            ]);

        $media = Media::where('name', 'like', "%{$q}%")
            ->limit($limit)
            ->get()
            ->map(fn ($m) => [
                'type' => 'Media',
                'label' => $m->name ?? $m->original_name ?? 'Media',
                'url' => route('admin.media.index'),
            ]);

        $users = User::where('name', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->limit($limit)
            ->get()
            ->map(fn ($u) => [
                'type' => 'User',
                'label' => $u->name,
                'url' => route('admin.users.edit', $u->id),
            ]);

        return response()->json([
            'results' => $posts->concat($pages)->concat($media)->concat($users)->take(12)->values(),
        ]);
    }

    /**
     * Notifications center: pending moderation, scheduled posts, recent activity.
     */
    public function notifications(Request $request)
    {
        $pendingComments = \App\Models\Comment::where('status', 'pending')
            ->with('post')
            ->latest()
            ->limit(5)
            ->get();

        $pendingPosts = Post::where('status', 'pending')
            ->where('post_type', 'post')
            ->latest()
            ->limit(5)
            ->get();

        $scheduledPosts = Post::where('post_type', 'post')
            ->where('published_at', '>', now())
            ->where('published_at', '<=', now()->addDays(7))
            ->latest()
            ->limit(5)
            ->get();

        $recentActivity = \App\Models\ActivityLog::latest()
            ->limit(10)
            ->get();

        $counts = [
            'pending_comments' => \App\Models\Comment::where('status', 'pending')->count(),
            'pending_posts' => Post::where('status', 'pending')->where('post_type', 'post')->count(),
            'scheduled_posts' => Post::where('post_type', 'post')
                ->where('published_at', '>', now())
                ->where('published_at', '<=', now()->addDays(7))
                ->count(),
        ];

        return view('admin.notifications.index', compact(
            'pendingComments', 'pendingPosts', 'scheduledPosts', 'recentActivity', 'counts'
        ));
    }
}
