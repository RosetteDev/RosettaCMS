<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Post;

class PageController extends PostController
{
    /**
     * Display a listing of pages.
     */
    public function index()
    {
        $pages = Post::where('post_type', 'page')
            ->with('user', 'site')
            ->paginate(20);
        
        return view('admin.pages.index', compact('pages'));
    }

    /**
     * Show the form for creating a new page.
     */
    public function create()
    {
        return view('admin.pages.form', [
            'action' => 'Create',
            'post' => new Post(),
        ]);
    }

    /**
     * Store a newly created page.
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
        ]);

        $page = Post::create([
            'user_id' => auth()->id(),
            'site_id' => $request->site_id ?? 1,
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? \Str::slug($validated['title']),
            'content' => $validated['content'] ?? null,
            'blocks' => $validated['blocks'] ?? null,
            'post_type' => 'page',  // Force page type
            'status' => $validated['status'] ?? 'draft',
        ]);

        return redirect()->route('admin.pages.edit', $page->id)
            ->with('success', 'Page created successfully.');
    }

    /**
     * Display the specified page.
     */
    public function show(Post $page)
    {
        // Ensure it's a page
        if ($page->post_type !== 'page') {
            return redirect()->route('admin.pages.index')
                ->with('error', 'This is not a page.');
        }

        return view('admin.pages.show', compact('page'));
    }

    /**
     * Show the form for editing the specified page.
     */
    public function edit(Post $page)
    {
        // Ensure it's a page
        if ($page->post_type !== 'page') {
            return redirect()->route('admin.pages.index')
                ->with('error', 'This is not a page.');
        }

        return view('admin.pages.form', [
            'action' => 'Edit',
            'post' => $page,
        ]);
    }

    /**
     * Update the specified page.
     */
    public function update(Request $request, Post $page)
    {
        // Ensure it's a page
        if ($page->post_type !== 'page') {
            return redirect()->route('admin.pages.index')
                ->with('error', 'This is not a page.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable',
            'blocks' => 'nullable|array',
            'slug' => 'nullable|string|max:255',
            'post_type' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
        ]);

        $page->update([
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? \Str::slug($validated['title']),
            'content' => $validated['content'] ?? null,
            'blocks' => $validated['blocks'] ?? null,
            'post_type' => 'page',  // Force page type
            'status' => $validated['status'] ?? 'draft',
        ]);

        return redirect()->route('admin.pages.edit', $page->id)
            ->with('success', 'Page updated successfully.');
    }

    /**
     * Remove the specified page.
     */
    public function destroy(Post $page)
    {
        // Ensure it's a page
        if ($page->post_type !== 'page') {
            return redirect()->route('admin.pages.index')
                ->with('error', 'This is not a page.');
        }

        $page->delete();
        return redirect()->route('admin.pages.index')
            ->with('success', 'Page deleted successfully.');
    }
}
