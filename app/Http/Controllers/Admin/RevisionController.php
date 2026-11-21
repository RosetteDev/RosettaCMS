<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RevisionController extends Controller
{
    /**
     * Display a listing of revisions for a post.
     */
    public function index(Post $post)
    {
        if ($post->post_type === 'revision') {
            return redirect()->route('admin.posts.index')
                ->with('error', 'This is a revision, not a post.');
        }

        $revisions = PostRevision::where('post_id', $post->id)
            ->with('user')
            ->orderBy('revision_date', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.revisions.index', compact('post', 'revisions'));
    }

    /**
     * Display a specific revision.
     */
    public function show(Post $post, PostRevision $revision)
    {
        // Ensure revision belongs to post
        if ($revision->post_id !== $post->id) {
            return redirect()->route('admin.posts.revisions.index', $post->id)
                ->with('error', 'Revision does not belong to this post.');
        }

        return view('admin.revisions.show', compact('post', 'revision'));
    }

    /**
     * Show the diff between two revisions.
     */
    public function diff(Post $post, Request $request)
    {
        $fromRevisionId = $request->input('from');
        $toRevisionId = $request->input('to');

        $fromRevision = PostRevision::find($fromRevisionId);
        $toRevision = PostRevision::find($toRevisionId);

        if (!$fromRevision || !$toRevision) {
            return redirect()->route('admin.posts.revisions.index', $post->id)
                ->with('error', 'Invalid revision selection.');
        }

        // Get content diff
        $diff = $this->generateDiff($fromRevision->content ?? '', $toRevision->content ?? '');

        return view('admin.revisions.diff', compact('post', 'fromRevision', 'toRevision', 'diff'));
    }

    /**
     * Restore a revision.
     */
    public function restore(Post $post, PostRevision $revision)
    {
        // Ensure revision belongs to post
        if ($revision->post_id !== $post->id) {
            return redirect()->route('admin.posts.revisions.index', $post->id)
                ->with('error', 'Revision does not belong to this post.');
        }

        // Create a new revision before restoring
        $currentRevision = new PostRevision();
        $currentRevision->post_id = $post->id;
        $currentRevision->user_id = auth()->id();
        $currentRevision->title = $post->title;
        $currentRevision->content = $post->content;
        $currentRevision->excerpt = $post->excerpt;
        $currentRevision->meta = $post->meta;
        $currentRevision->revision_type = 'manual';
        $currentRevision->save();

        // Restore the revision
        $post->title = $revision->title;
        $post->content = $revision->content;
        $post->excerpt = $revision->excerpt;
        $post->meta = $revision->meta;
        $post->save();

        return redirect()->route('admin.posts.edit', $post->id)
            ->with('success', 'Revision restored successfully.');
    }

    /**
     * Generate a text diff between two strings.
     */
    protected function generateDiff(string $from, string $to): array
    {
        $fromLines = preg_split('/\r?\n/', $from);
        $toLines = preg_split('/\r?\n/', $to);

        $result = [];
        $fromIndex = 0;
        $toIndex = 0;

        while ($fromIndex < count($fromLines) || $toIndex < count($toLines)) {
            if ($fromIndex < count($fromLines) && $toIndex < count($toLines) && $fromLines[$fromIndex] === $toLines[$toIndex]) {
                $result[] = [
                    'type' => 'unchanged',
                    'from' => $fromLines[$fromIndex],
                    'to' => $toLines[$toIndex],
                ];
                $fromIndex++;
                $toIndex++;
            } elseif ($toIndex >= count($toLines) || ($fromIndex < count($fromLines) && $this->longestCommonSubsequence($fromLines, $toLines, $fromIndex, $toIndex) > 0)) {
                $result[] = [
                    'type' => 'deleted',
                    'from' => $fromLines[$fromIndex],
                    'to' => null,
                ];
                $fromIndex++;
            } else {
                $result[] = [
                    'type' => 'added',
                    'from' => null,
                    'to' => $toLines[$toIndex],
                ];
                $toIndex++;
            }
        }

        return $result;
    }

    /**
     * Simple LCS (Longest Common Subsequence) helper for diff generation.
     */
    protected function longestCommonSubsequence(array $fromLines, array $toLines, int $fromIndex, int $toIndex): int
    {
        $lcs = 0;
        while ($fromIndex + $lcs < count($fromLines) && $toIndex + $lcs < count($toLines) && $fromLines[$fromIndex + $lcs] === $toLines[$toIndex + $lcs]) {
            $lcs++;
        }
        return $lcs;
    }
}
