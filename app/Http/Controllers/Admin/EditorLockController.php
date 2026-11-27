<?php

namespace App\Http\Controllers\Admin;

use App\Models\Post;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Support\EditorLock;
use Illuminate\Http\Request;

class EditorLockController extends Controller
{
    /**
     * Acquire or refresh the current user's lock on a post.
     *
     * Never steals a lock held by someone else; returns the conflicting holder
     * in the response instead.
     */
    public function acquire(Request $request, Post $post)
    {
        $this->enforceEdit($post);

        $user = $request->user();
        $conflict = EditorLock::conflictingLock($post, $user);

        if ($conflict) {
            return response()->json([
                'holder' => ['id' => $conflict->user->id, 'name' => $conflict->user->name],
                'me' => false,
                'conflict' => true,
            ]);
        }

        $lock = EditorLock::acquire($post, $user);

        return response()->json([
            'holder' => ['id' => $lock->user->id, 'name' => $lock->user->name],
            'me' => true,
            'conflict' => false,
        ]);
    }

    /**
     * Release the current user's lock on a post.
     */
    public function release(Request $request, Post $post)
    {
        $this->enforceEdit($post);

        EditorLock::release($post, $request->user());

        return response()->json(['released' => true]);
    }

    /**
     * Report the current lock holder for a post (does not create one).
     */
    public function show(Request $request, Post $post)
    {
        $this->enforceEdit($post);

        $holder = EditorLock::holder($post);

        return response()->json([
            'holder' => $holder ? ['id' => $holder->id, 'name' => $holder->name] : null,
            'me' => $holder && $holder->id === $request->user()->id,
        ]);
    }

    /**
     * Abort 403 when the current user cannot edit this post's type.
     */
    protected function enforceEdit(Post $post): void
    {
        $type = $post->post_type ?? 'post';

        if (!auth()->user()->canPerform($type, 'edit')) {
            abort(403, 'You do not have permission to edit this ' . ($type === 'page' ? 'page' : 'post') . '.');
        }
    }
}
