<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    /**
     * Display a listing of all comments.
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $search = $request->input('search');
        $postId = $request->input('post_id');
        
        $query = Comment::with(['post', 'user', 'parent']);
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                  ->orWhere('author_name', 'like', "%{$search}%")
                  ->orWhere('author_email', 'like', "%{$search}%");
            });
        }
        
        if ($postId) {
            $query->where('post_id', $postId);
        }
        
        $comments = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        $statistics = $this->getStatistics();
        
        return view('admin.comments.index', compact('comments', 'status', 'search', 'statistics'));
    }
    
    /**
     * Display pending comments (moderation queue).
     */
    public function pending()
    {
        $comments = Comment::with(['post', 'user', 'parent'])
            ->pending()
            ->orderBy('created_at', 'asc')
            ->paginate(20);
            
        $statistics = $this->getStatistics();
        
        return view('admin.comments.pending', compact('comments', 'statistics'));
    }
    
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $comment = Comment::with(['post', 'user'])->findOrFail($id);
        return view('admin.comments.edit', compact('comment'));
    }
    
    /**
     * Approve a comment.
     */
    public function approve(string $id)
    {
        $comment = Comment::findOrFail($id);
        $comment->update(['status' => 'approved']);
        
        return redirect()
            ->back()
            ->with('success', 'Comment approved successfully.');
    }
    
    /**
     * Reject a comment.
     */
    public function reject(string $id)
    {
        $comment = Comment::findOrFail($id);
        $comment->update(['status' => 'rejected']);
        
        return redirect()
            ->back()
            ->with('error', 'Comment rejected.');
    }
    
    /**
     * Mark comment as spam.
     */
    public function spam(string $id)
    {
        $comment = Comment::findOrFail($id);
        $comment->update(['status' => 'spam']);
        
        return redirect()
            ->back()
            ->with('warning', 'Comment marked as spam.');
    }
    
    /**
     * Delete a comment.
     */
    public function destroy(string $id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();
        
        return redirect()
            ->route('admin.comments.index')
            ->with('success', 'Comment deleted successfully.');
    }
    
    /**
     * Reply to a comment.
     */
    public function reply(Request $request, string $id)
    {
        $request->validate([
            'content' => 'required|string',
        ]);
        
        $comment = Comment::findOrFail($id);
        
        $reply = new Comment();
        $reply->post_id = $comment->post_id;
        $reply->parent_id = $comment->id;
        $reply->user_id = auth()->id();
        $reply->content = $request->content;
        $reply->status = 'approved'; // Admin replies auto-approve
        $reply->save();
        
        return redirect()
            ->route('admin.comments.edit', $comment->id)
            ->with('success', 'Reply added successfully.');
    }
    
    /**
     * Bulk approve comments.
     */
    public function bulkApprove(Request $request)
    {
        $ids = $request->input('ids', []);
        Comment::whereIn('id', $ids)->update(['status' => 'approved']);
        
        return redirect()
            ->back()
            ->with('success', count($ids) . ' comments approved successfully.');
    }
    
    /**
     * Bulk reject comments.
     */
    public function bulkReject(Request $request)
    {
        $ids = $request->input('ids', []);
        Comment::whereIn('id', $ids)->update(['status' => 'rejected']);
        
        return redirect()
            ->back()
            ->with('error', count($ids) . ' comments rejected.');
    }
    
    /**
     * Bulk delete comments.
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        Comment::whereIn('id', $ids)->delete();
        
        return redirect()
            ->back()
            ->with('success', count($ids) . ' comments deleted successfully.');
    }
    
    /**
     * Get comment statistics.
     */
    private function getStatistics(): array
    {
        $total = Comment::count();
        $approved = Comment::approved()->count();
        $pending = Comment::pending()->count();
        $rejected = Comment::rejected()->count();
        $spam = Comment::spam()->count();
        
        return compact('total', 'approved', 'pending', 'rejected', 'spam');
    }
}
