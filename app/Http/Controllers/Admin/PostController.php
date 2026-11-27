<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\SavesPostContent;
use App\Models\SiteSetting;
use App\Traits\SanitizeContent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Post;
use App\Models\Category;
use App\Models\Language;
use App\Models\LanguageContent;
use App\Models\Tag;

class PostController extends Controller
{
    use SanitizeContent, SavesPostContent;

    /**
     * Abort 403 when the current user lacks the given permission for posts.
     */
    protected function enforce(string $action): void
    {
        if (!auth()->user()->canPerform('post', $action)) {
            abort(403, 'You do not have permission to ' . $action . ' posts.');
        }
    }

    /**
     * Display a listing of posts.
     */
    public function index(Request $request)
    {
        $this->enforce('view');

        $search = trim((string) $request->input('search'));

        $posts = Post::where('post_type', 'post')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%')
                        ->orWhere('content', 'like', '%' . $search . '%');
                });
            })
            ->with('user', 'site')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $settings = SiteSetting::forSite();
        $users = \App\Models\User::orderBy('name')->get();

        return view('admin.posts.index', compact('posts', 'settings', 'users'));
    }

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        $this->enforce('create');

        if (!auth()->user()->canEditField('post', 'title')) {
            abort(403, 'You do not have permission to edit the title of posts.');
        }

        return view('admin.posts.form', [
            'action' => 'Create',
            'post' => new Post(),
            'postType' => 'post',
            'editableFields' => auth()->user()->editableFieldsFor('post'),
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created post.
     */
    public function store(Request $request)
    {
        $this->enforce('create');

        if (!auth()->user()->canEditField('post', 'title')) {
            abort(403, 'You do not have permission to edit the title of posts.');
        }

        $post = new Post();
        $post->user_id = auth()->id();

        $this->savePost($request, $post, 'post');

        return redirect()->route('admin.posts.edit', $post->id)
            ->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified post.
     */
    public function show(Post $post)
    {
        $this->enforce('view');

        if ($post->post_type !== 'post') {
            return redirect()->route('admin.posts.index')
                ->with('error', 'This is not a post.');
        }

        return view('admin.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Post $post)
    {
        $this->enforce('edit');

        if ($post->post_type !== 'post') {
            return redirect()->route('admin.posts.index')
                ->with('error', 'This is not a post.');
        }

        return view('admin.posts.form', [
            'action' => 'Edit',
            'post' => $post,
            'postType' => 'post',
            'editableFields' => auth()->user()->editableFieldsFor('post'),
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, Post $post)
    {
        $this->enforce('edit');

        if ($post->post_type !== 'post') {
            return redirect()->route('admin.posts.index')
                ->with('error', 'This is not a post.');
        }

        $lock = \App\Support\EditorLock::conflictingLock($post, auth()->user());
        if ($lock) {
            return redirect()->route('admin.posts.edit', $post->id)
                ->withInput()
                ->with('error', 'This post is being edited by ' . $lock->user->name . '. Your changes were not saved — please wait for them to finish, then try again.');
        }

        $this->savePost($request, $post, 'post');

        return redirect()->route('admin.posts.edit', $post->id)
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified post.
     */
    public function destroy(Post $post)
    {
        $this->enforce('delete');

        if ($post->post_type !== 'post') {
            return redirect()->route('admin.posts.index')
                ->with('error', 'This is not a post.');
        }

        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Handle bulk operations on posts
     */
    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|exists:posts,id',
            'action' => 'required|in:delete,publish,unpublish,trash,restore,assign',
            'assign_user_id' => 'nullable|exists:users,id',
        ]);

        if ($validated['action'] === 'assign' && empty($validated['assign_user_id'])) {
            return response()->json(['success' => false, 'message' => 'Choose an author to assign.'], 422);
        }

        $posts = Post::whereIn('id', $validated['ids'])->get();

        switch ($validated['action']) {
            case 'delete':
                foreach ($posts as $post) {
                    $post->delete();
                }
                return response()->json(['success' => true, 'message' => count($posts) . ' posts permanently deleted.']);
            
            case 'publish':
                foreach ($posts as $post) {
                    $post->status = 'published';
                    if (!$post->published_at) {
                        $post->published_at = now();
                    }
                    $post->save();
                }
                return response()->json(['success' => true, 'message' => count($posts) . ' posts published.']);
            
            case 'unpublish':
                foreach ($posts as $post) {
                    $post->status = 'draft';
                    $post->published_at = null;
                    $post->save();
                }
                return response()->json(['success' => true, 'message' => count($posts) . ' posts unpublished (drafted).']);
            
            case 'trash':
                foreach ($posts as $post) {
                    $post->status = 'trash';
                    $post->save();
                }
                return response()->json(['success' => true, 'message' => count($posts) . ' posts moved to trash.']);
            
            case 'assign':
                foreach ($posts as $post) {
                    $post->user_id = $validated['assign_user_id'];
                    $post->save();
                }
                return response()->json(['success' => true, 'message' => count($posts) . ' post(s) assigned to ' . \App\Models\User::find($validated['assign_user_id'])?->name . '.']);

            case 'restore':
                foreach ($posts as $post) {
                    $post->status = 'draft';
                    $post->save();
                }
                return response()->json(['success' => true, 'message' => count($posts) . ' posts restored from trash.']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action.'], 400);
    }
}
