<?php

namespace App\Http\Controllers\Admin;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use App\Models\Post;

class PageController extends PostController
{
    /**
     * Display a listing of pages.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $pages = Post::where('post_type', 'page')
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

        return view('admin.pages.index', compact('pages', 'settings'));
    }

    /**
     * Show the form for creating a new page.
     */
    public function create()
    {
        return view('admin.pages.form', [
            'action' => 'Create',
            'post' => new Post(),
            'postType' => 'page',
            'categories' => collect(),
            'tags' => collect(),
        ]);
    }

    /**
     * Store a newly created page.
     */
    public function store(Request $request)
    {
        $page = new Post();
        $page->user_id = auth()->id();

        $this->savePost($request, $page, 'page');

        return redirect()->route('admin.pages.edit', $page->id)
            ->with('success', 'Page created successfully.');
    }

    /**
     * Display the specified page.
     */
    public function show(Post $page)
    {
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
        if ($page->post_type !== 'page') {
            return redirect()->route('admin.pages.index')
                ->with('error', 'This is not a page.');
        }

        return view('admin.pages.form', [
            'action' => 'Edit',
            'post' => $page,
            'postType' => 'page',
            'categories' => collect(),
            'tags' => collect(),
        ]);
    }

    /**
     * Update the specified page.
     */
    public function update(Request $request, Post $page)
    {
        if ($page->post_type !== 'page') {
            return redirect()->route('admin.pages.index')
                ->with('error', 'This is not a page.');
        }

        $this->savePost($request, $page, 'page');

        return redirect()->route('admin.pages.edit', $page->id)
            ->with('success', 'Page updated successfully.');
    }

    /**
     * Remove the specified page.
     */
    public function destroy(Post $page)
    {
        if ($page->post_type !== 'page') {
            return redirect()->route('admin.pages.index')
                ->with('error', 'This is not a page.');
        }

        $page->delete();

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page deleted successfully.');
    }
}
