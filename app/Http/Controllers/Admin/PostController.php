<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;

class PostController extends Controller
{
    /**
     * Display a listing of posts.
     */
    public function index()
    {
        // Filter posts to exclude pages (post_type = 'page')
        $posts = Post::where('post_type', 'post')
            ->with('user', 'site')
            ->paginate(20);
        return view('admin.posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();
        
        return view('admin.posts.form', [
            'action' => 'Create',
            'post' => new Post(),
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    /**
     * Store a newly created post.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable',
            'blocks' => 'nullable|array',
            'slug' => 'nullable|string|max:255|unique:posts',
            'post_type' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
            'site_id' => 'nullable|exists:sites,id',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        $post = Post::create([
            'user_id' => auth()->id(),
            'site_id' => $request->site_id ?? 1, // Default to first site
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? \Str::slug($validated['title']),
            'content' => $validated['content'] ?? null,
            'blocks' => $validated['blocks'] ?? null,
            'post_type' => $validated['post_type'] ?? 'post',
            'status' => $validated['status'] ?? 'draft',
        ]);

        // Attach categories
        if (isset($validated['categories'])) {
            $post->categories()->sync($validated['categories']);
        }

        // Attach tags
        if (isset($validated['tags'])) {
            $post->tags()->sync($validated['tags']);
        }

        return redirect()->route('admin.posts.edit', $post->id)
            ->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified post.
     */
    public function show(Post $post)
    {
        return view('admin.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Post $post)
    {
        $categories = Category::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();
        
        return view('admin.posts.form', [
            'action' => 'Edit',
            'post' => $post,
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable',
            'blocks' => 'nullable|array',
            'slug' => 'nullable|string|max:255',
            'post_type' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        $post->update([
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? \Str::slug($validated['title']),
            'content' => $validated['content'] ?? null,
            'blocks' => $validated['blocks'] ?? null,
            'post_type' => $validated['post_type'] ?? 'post',
            'status' => $validated['status'] ?? 'draft',
        ]);

        // Sync categories
        if (isset($validated['categories'])) {
            $post->categories()->sync($validated['categories']);
        }

        // Sync tags
        if (isset($validated['tags'])) {
            $post->tags()->sync($validated['tags']);
        }

        return redirect()->route('admin.posts.edit', $post->id)
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified post.
     */
    public function destroy(Post $post)
    {
        $post->delete();
        return redirect()->route('admin.posts.index')
            ->with('success', 'Post deleted successfully.');
    }
}
